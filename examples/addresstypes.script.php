<?php

use BitWasp\Bitcoin\Address\AddressCreator;
use BitWasp\Bitcoin\Key\Factory\PrivateKeyFactory;
use BitWasp\Bitcoin\Script\P2shScript;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\WitnessScript;
use BitWasp\Bitcoin\Bitcoin;

use CoinParams\BitWasp\MultiCoinNetwork;

require __DIR__ . "/../vendor/autoload.php";

Bitcoin::setNetwork( new MultiCoinNetwork('LTC') );

$privFactory = PrivateKeyFactory::compressed();
$priv = $privFactory->fromWif('T6XjwABVEGZFNGtzZAqECW8H3DRXzNpa9ZpLDimuTyVTNPF3ALJH');
$publicKey = $priv->getPublicKey();
$pubKeyHash = $publicKey->getPubKeyHash();

$script = ScriptFactory::scriptPubKey()->p2pkh($pubKeyHash);

### Key hash types
echo "key hash types\n";
$addrReader = new AddressCreator();
$p2pkh = $addrReader->fromOutputScript($script);
echo " * p2pkh address: {$p2pkh->getAddress()}\n";

#### Script hash types

echo "\nscript hash types:\n";
// taking an available script to be another addresses redeem script..
$redeemScript = new P2shScript($p2pkh->getScriptPubKey());
$p2shAddr = $redeemScript->getAddress();
echo " * p2sh: {$p2shAddr->getAddress()}\n";


$p2wshScript = new WitnessScript($p2pkh->getScriptPubKey());
$p2wshAddr = $p2wshScript->getAddress();
echo " * p2wsh: {$p2wshAddr->getAddress()}\n";

$p2shP2wshScript = new P2shScript(new WitnessScript($p2pkh->getScriptPubKey()));
$p2shP2wshAddr = $p2shP2wshScript->getAddress();
echo " * p2sh|p2wsh: {$p2shP2wshAddr->getAddress()}\n";
