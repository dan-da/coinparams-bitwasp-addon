<?php

require_once __DIR__ . "/../vendor/autoload.php";

use BitWasp\Bitcoin\Crypto\Random\Random;
use BitWasp\Bitcoin\Key\Factory\HierarchicalKeyFactory;
use BitWasp\Bitcoin\Mnemonic\Bip39\Bip39SeedGenerator;
use BitWasp\Bitcoin\Mnemonic\MnemonicFactory;
use BitWasp\Bitcoin\Key\Deterministic\Slip132\Slip132;
use BitWasp\Bitcoin\Key\KeyToScript\KeyToScriptHelper;
use BitWasp\Bitcoin\Bitcoin;
use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\GlobalPrefixConfig;
use BitWasp\Bitcoin\Key\Deterministic\HdPrefix\NetworkConfig;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\Base58ExtendedKeySerializer;
use BitWasp\Bitcoin\Serializer\Key\HierarchicalKey\ExtendedKeySerializer;


use CoinParams\BitWasp\MultiCoinNetwork;
use CoinParams\BitWasp\MultiCoinRegistry;

Bitcoin::setNetwork( new MultiCoinNetwork('LTC') );

// for LTC xpub prefixes.
$extendedPrefixes = new MultiCoinRegistry('LTC');

// LTC has 2 sets of extended key prefixes:
//   Ltub:  used by many 3rd party wallets.
//   xpub: used by litecoin-core and the default in CoinParams.
// Here we demonstrate how to use the Ltub prefixes instead.
// Comment out this line to use the xpub prefixes.
$extendedPrefixes = new MultiCoinRegistry('LTC', 'main', ['alternate' => 'Ltub']);

// If you want to produce different addresses,
// set a different prefix/factory here.
$slip132 = new Slip132(new KeyToScriptHelper(Bitcoin::getEcAdapter()));
$prefix = $slip132->p2pkh($extendedPrefixes);
$scriptFactory = $prefix->getScriptDataFactory();

// Generate a mnemonic
$random = new Random();
$entropy = $random->bytes(64);

$bip39 = MnemonicFactory::bip39();
$seedGenerator = new Bip39SeedGenerator();
$mnemonic = $bip39->entropyToMnemonic($entropy);

// Derive a seed from mnemonic/password
$seed = $seedGenerator->getSeed($mnemonic, 'password');
echo "\n" . '  Seed: ' . $seed->getHex() . "\n\n";

$hdFactory = new HierarchicalKeyFactory();
$bip32 = $hdFactory->fromEntropy($seed, $scriptFactory);

$config = new GlobalPrefixConfig([new NetworkConfig(Bitcoin::getNetwork(), [$prefix]),]);
$serializer = new Base58ExtendedKeySerializer(new ExtendedKeySerializer(Bitcoin::getEcAdapter(), $config));
echo "  root priv key: " . $serializer->serialize(Bitcoin::getNetwork(), $bip32) . "\n\n";
echo "  root pub key: " . $serializer->serialize(Bitcoin::getNetwork(), $bip32->withoutPrivateKey()) . "\n\n";


$addrCreator = new AddressCreator();
echo '  root Address: ' . $bip32->getAddress($addrCreator)->getAddress(Bitcoin::getNetwork()) . "\n\n";
