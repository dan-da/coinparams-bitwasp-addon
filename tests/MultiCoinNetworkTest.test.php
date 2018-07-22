<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

use CoinParams\BitWasp\MultiCoinNetwork;

class MultiCoinNetworkTest extends test_base {
    
    public function runtests() {
        $this->btc_mainnet();
    }
    
    protected function btc_mainnet() {
        $chain = 'BTC-main';
        $network = new MultiCoinNetwork('BTC');

        $this->eq( $network->getSignedMessageMagic(), "\x18Bitcoin Signed Message:\n", "$chain: SignedMessageMagic");
        $this->eq( $network->getNetMagicBytes(), 'f9beb4d9', "$chain: getNetMagicBytes");
        $this->eq( $network->getPrivByte(), '80', "$chain: getPrivByte");
        $this->eq( $network->getAddressByte(), '00', "$chain: getAddressByte");
        $this->eq( $network->getAddressPrefixLength(), 1, "$chain: getAddressPrefixLength");
        $this->eq( $network->getP2shByte(), '05', "$chain: getP2shByte");
        $this->eq( $network->getP2shPrefixLength(), 1, "$chain: getP2shPrefixLength");
        $this->eq( $network->getHDPubByte(), '0488b21e', "$chain: getHDPubByte");
        $this->eq( $network->getHDPrivByte(), '0488ade4', "$chain: getHDPrivByte");
        $this->eq( $network->getSegwitBech32Prefix(), 'bc', "$chain: getSegwitBech32Prefix");
    }
}
