<?php

declare(strict_types=1);

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;

use CoinParams\BitWasp\MultiCoinNetwork;
use CoinParams\BitWasp\MultiCoinRegistry;

require __DIR__ . "/../vendor/autoload.php";

$adapter = Bitcoin::getEcAdapter();
$network = new MultiCoinNetwork('LCC');

Bitcoin::setNetwork($network);

$slip132 = new Slip132(new KeyToScriptHelper($adapter));

// we set option "undefined_used_btc", which means that Bitcoin extended key
// prefixes will be used when undefined by slip132
$extPrefixes = new MultiCoinRegistry('LCC', 'main', ['undefined_used_btc' => true]);
$zpubPrefix = $slip132->p2wpkh($extPrefixes);

$config = new GlobalPrefixConfig([
    new NetworkConfig($network, [
        $zpubPrefix,
    ])
]);

$serializer = new Base58ExtendedKeySerializer(
    new ExtendedKeySerializer($adapter, $config)
);

$rootKey = $serializer->parse($network, "zprvAWgYBBk7JR8GinKQsQP2L5uFDZ4oz7rpRnRhq2WQaKuTkPJeZDWAzXjyWMiFmivaJhr59usUiWoJCYZRDq3KEvj8rXXn5CMrGCrCkdZ6nyg");

$account0Key = $rootKey->derivePath("84'/0'/0'");
$firstKey = $account0Key->derivePath("0/0");
$address = $firstKey->getAddress(new AddressCreator());
echo $address->getAddress() . PHP_EOL;
