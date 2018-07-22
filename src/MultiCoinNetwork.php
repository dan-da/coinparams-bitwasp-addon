<?php

namespace CoinParams\BitWasp;

use BitWasp\Bitcoin\Network\Network;
use BitWasp\Bitcoin\Script\ScriptType;
use CoinParams\CoinParams;

class MultiCoinNetwork extends Network {
    
    protected $base58PrefixMap;
    protected $bip32PrefixMap;
    protected $bip32ScriptTypeMap;
    protected $signedMessagePrefix;
    protected $bech32PrefixMap;
    protected $p2pMagic;
    
    protected $symbol;
    protected $network;

    /**
     * @param string $symbol
     * @param string $network  [main, test, regtest]
     */
    function __construct($symbol, $network='main') {

        // stash these away in case an inherited class needs them.
        $this->symbol = $symbol;
        $this->network = $network;

        // retrieve data for this blockchain from coinParams json file.
        // see https://github.com/dan-da/coinparams
        $params = CoinParams::get_coin_network($symbol, $network);
        
        // These are all protected methods, so override if you need to.
        $this->base58PrefixMap     = $this->genBase58PrefixMap($params);
        $this->bech32PrefixMap     = $this->genBech32PrefixMap($params);
        $this->bip32PrefixMap      = $this->genBip32PrefixMap($params);
        $this->bip32ScriptTypeMap  = $this->genBip32ScriptTypeMap($params);
        $this->signedMessagePrefix = $this->genSignedMessagePrefix($params);
        $this->p2pMagic            = $this->genP2pMagic($params);
    }
    
    /**
     * @param array $params  blockchain data from coinParams::get_coin_network()
     */
    protected function genBase58PrefixMap($params) {
        
        $prefixes = @$params['prefixes'];
        
        // Prefer scripthash2 to scripthash. For coins like LTC that
        // changed p2sh prefix after-launch to differentiate from BTC.
        // Overrid this method if you need a different behavior.
        $scripthash = @$prefixes['scripthash2'] ?
                       $prefixes['scripthash2'] : $prefixes['scripthash'];
        
        return [
            self::BASE58_ADDRESS_P2PKH => $this->dh(@$prefixes['public']),
            self::BASE58_ADDRESS_P2SH => $this->dh($scripthash),
            self::BASE58_WIF => $this->dh(@$prefixes['private']),
        ];
    }
    
    /**
     * @param array $params  blockchain data from coinParams::get_coin_network()
     */
    protected function genBech32PrefixMap($params) {
        $map = [];
        if( @$params['prefixes']['bech32'] ) {
            $map[self::BECH32_PREFIX_SEGWIT] = @$params['prefixes']['bech32'];
        }
        return $map;
    }
    

    /**
     * @param array $params  blockchain data from coinParams::get_coin_network()
     */
    protected function genBip32PrefixMap($params) {
        return [
            self::BIP32_PREFIX_XPUB => $this->nh(@$params['prefixes']['extended']['xpub']['public']),
            self::BIP32_PREFIX_XPRV => $this->nh(@$params['prefixes']['extended']['xpub']['private']),
        ];
    }
    
    /**
     * @param array $params  blockchain data from coinParams::get_coin_network()
     */
    protected function genBip32ScriptTypeMap($params) {
        return [
            self::BIP32_PREFIX_XPUB => ScriptType::P2PKH,
            self::BIP32_PREFIX_XPRV => ScriptType::P2PKH,
        ];
    }
    
    /**
     * @param array $params  blockchain data from coinParams::get_coin_network()
     */
    protected function genSignedMessagePrefix($params) {
        return $params['message_magic'];
    }
    
    /**
     * @param array $params  blockchain data from coinParams::get_coin_network()
     */
    protected function genP2pMagic($params) {
        return $this->nh(@$params['protocol']['magic']);
    }
    
    /**
     *  normalizes a hex string.
     *  
     *  coinparams incoming values look like 0x1ec
     *  but bitwasp lib expects them like
     *  01ec or ec instead.  this method drops the 0x
     *  and prepends 0 if necessary to make length an even number.
     *
     *  @param string $hex
     *  
     *  @return string normalized hex string.
     */
    private function nh($hex) {
        $hex = substr($hex,0,2) == '0x' ? substr($hex, 2) : $hex;
        $pre = strlen($hex) % 2 == 0 ? '' : '0';
        return $pre . $hex;
    }

    /**
     * converts a decimal to normalized hex string.
     *
     * @param integer $dec
     *
     * @return normalized hex string
     */
    private function dh($dec) {
        return $this->nh(dechex($dec));
    }
}

