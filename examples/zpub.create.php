<?php

declare(strict_types=1);

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Key\Deterministic\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;

use CoinParams\BitWasp\MultiCoinNetwork;
use CoinParams\BitWasp\MultiCoinRegistry;

require __DIR__ . "/../vendor/autoload.php";

$adapter = Bitcoin::getEcAdapter();
$addrCreator = new AddressCreator();

// We're using litecoin and want the zpub.
// Grab litecoin registry, use that to make our prefix.
$network = new MultiCoinNetwork('LCC');
Bitcoin::setNetwork($network);

// we set option "undefined_used_btc", which means that Bitcoin extended key
// prefixes will be used when undefined by slip132
$extPrefixes = new MultiCoinRegistry('LCC', 'main', ['undefined_used_btc' => true]);

// If you want to produce different addresses,
// set a different prefix/factory here.
$slip132 = new Slip132(new KeyToScriptHelper($adapter));
$prefix = $slip132->p2wpkh($extPrefixes);
$scriptFactory = $prefix->getScriptDataFactory();

// To create a key and derive addressses, we don't
// need the GlobalPrefixConfig, or even a ScriptPrefix.
// We just need a ScriptDataFactory. (see the KeyToScript
// helpers on how to create custom script factories)

$random = new Random();
$hdFactory = new HierarchicalKeyFactory($adapter);
$masterKey = $hdFactory->generateMasterKey($adapter, $scriptFactory);

// First nice part, we have access to the SPK/RS/WS
$scriptAndSignData = $masterKey->getScriptAndSignData();
$spk = $scriptAndSignData->getScriptPubKey();
$signData = $scriptAndSignData->getSignData();
echo "scriptPubKey: " . $spk->getHex() . PHP_EOL;
if ($signData->hasRedeemScript()) {
    echo "redeemScript: " . $signData->getRedeemScript()->getHex().PHP_EOL;
}
if ($signData->hasWitnessScript()) {
    echo "witnessScript: " . $signData->getWitnessScript()->getHex().PHP_EOL;
}

// Drawing on the spk, we can try and make an address
$address = $masterKey->getAddress($addrCreator);
echo "address: " . $address->getAddress($network) . PHP_EOL;

// Doh - you wanna serialize NOW?
// Well, the toExtendedKey() method will error because
// the HK's serializer doesn't know about the GlobalPrefixConfig.
// So we need to bring our own serializer, configurable
// with the bare minimum prefixes.
try {
    $masterKey->toExtendedKey();
} catch (\Exception $e) {
    echo "\nfriendly reminder: {$e->getMessage()}\n\n";
    // "Cannot serialize non-P2PKH HierarchicalKeys without a GlobalPrefixConfig"
}

$config = new GlobalPrefixConfig([
    new NetworkConfig($network, [
        $prefix
    ]),
]);

$serializer = new Base58ExtendedKeySerializer(new ExtendedKeySerializer($adapter, $config));

$serialized = $serializer->serialize($network, $masterKey);
echo "master key: " . $serialized . PHP_EOL;
