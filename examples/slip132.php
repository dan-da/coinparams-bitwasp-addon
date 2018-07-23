<?php

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Network\Slip132\BitcoinRegistry;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;

use CoinParams\BitWasp\MultiCoinNetwork;
use CoinParams\BitWasp\MultiCoinRegistry;

require __DIR__ . "/../vendor/autoload.php";

$adapter = Bitcoin::getEcAdapter();
$slip132 = new Slip132(new KeyToScriptHelper($adapter));
$addrCreator = new AddressCreator();

// We're using Bitcoin Gold (BTG), and need the slip132 bitcoin registry
$network = new MultiCoinNetwork('BTG');

// we set option "undefined_used_btc", which means that Bitcoin extended key
// prefixes will be used when undefined by slip132
$extPrefixes = new MultiCoinRegistry('BTG', 'main', ['undefined_used_btc' => true]);

// What prefixes do we want to encode/decode? Configure those here
// Separate out this one, want it in a sec
$ypubPrefix = $slip132->p2shP2wpkh($extPrefixes);

// Keys with ALL of these prefixes will be supported.
// You can chose a subset if desired (for some networks it's
// a good idea!)
$config = new GlobalPrefixConfig([
    new NetworkConfig($network, [
        $slip132->p2pkh($extPrefixes),
        // $slip132->p2shP2pkh($extPrefixes),
        // ^^ that's why this is so configurable.
        // prefixes can conflict, so you might need
        // two configs for full support ;)

        $ypubPrefix,
        $slip132->p2wpkh($extPrefixes),
    ])
]);

$networkPrefixConfig = $config->getNetworkConfig($network);
$serializer = new Base58ExtendedKeySerializer(new ExtendedKeySerializer($adapter, $config));

$bip39 = new Bip39SeedGenerator();
$seed = $bip39->getSeed("insect issue net wall milk bulb stamp remind tell fee roast mansion angry stable oil");

// This shows how we create such keys. You
// don't actually need the config until serialize
// time
$hdFactory = new HierarchicalKeyFactory($adapter);
$p2shP2wshP2pkhKey = $hdFactory->fromEntropy($seed, $adapter, $ypubPrefix->getScriptDataFactory());
$serialized = $serializer->serialize($network, $p2shP2wshP2pkhKey);
echo "master key {$serialized}\n";

// This shows how you can parse such a key.
// Remember the serializer needs the config for this!
$parsedKey = $serializer->parse($network, $serialized);
$accountKey = $parsedKey->derivePath("m/44'/0'/0'"); // Can't really remember the 'purpose' field for this script, assume 44
$serAccKey = $serializer->serialize($network, $accountKey);
echo "account key {$serAccKey}\n";

$addrKey = $accountKey->derivePath("0/0");
$serAddrKey = $serializer->serialize($network, $addrKey);
echo "address key {$serAddrKey}\n";
echo "addr[0] {$addrKey->getAddress($addrCreator)->getAddress($network)}\n";
