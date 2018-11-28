<?php

namespace CoinParams\BitWasp;

use BitWasp\Bitcoin\Key\Deterministic\Slip132\PrefixRegistry;
use BitWasp\Bitcoin\Script\ScriptType;
use CoinParams\CoinParams;

class MultiCoinRegistry extends PrefixRegistry
{
    private $key_type_map;
    
    protected $symbol;
    protected $network;
    protected $options;

    /**
     * @param string $symbol
     * @param string $network [main, test, regtest]
     * @param null $options
     * @throws \CoinParams\Exceptions\ArrayKeyNotFound
     */
    public function __construct($symbol, $network='main', $options=null) {
        // stash these away in case an inherited class needs them.
        $this->symbol = $symbol;
        $this->network = $network;
        $this->options = $options ?: ['undefined_used_btc' => false];
                
        // retrieve data for this blockchain from coinParams json file.
        // see https://github.com/dan-da/coinparams
        $params = CoinParams::get_coin_network($symbol, $network);

        $extended_map = Internal::aget($params['prefixes'], 'extended' , 'Extended key prefix map not found.');

        $alt = Internal::aget($options, 'alternate');

        if (!is_null($alt)) {
            $extended_map = $params['prefixes']['extended']['alternates'][$alt];
        }

        $map = $this->genMap($extended_map, $options);

        parent::__construct($map);
    }

    /**
     * Generates a map to be passed to PrefixRegistry::__construct()
     *
     * @param array $extended_map coinParams extended key prefix map
     *
     * extended_map should look like:
     * {
     *    "xpub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "ypub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "zpub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "Ypub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    },
     *    "Zpub": {
     *      "public": "0xXXXXXXXX",
     *      "private": "0xXXXXXXXX"
     *    }
     * }
     *
     * @return array a map to be passed to PrefixRegistry::__construct()
     * @throws \CoinParams\Exceptions\ArrayKeyNotFound
     */
    protected function genMap($extended_map, $options)
    {
        $em = $extended_map;
        $map = [];
        $t = [];
        $m = [];

        // remove BTC default values from extended keys of other coins
        // if requested.
        foreach(['x','X','y','Y','z','Z'] as $k) {
            $kpub = $k == 'X' ? 'xpub' : $k . 'pub';
            if( !Internal::aget($em[$kpub], 'undefined_used_btc') || Internal::aget($options , 'undefined_used_btc')) {
                $m[$k] = $em[$kpub];
            }
            else {
                $m[$k] = null;
            }
        }

        // keep for later.
        $this->key_type_map = $m;
        
        $st = [
            'x'  => [ScriptType::P2PKH],
            'X'  => [ScriptType::P2SH, ScriptType::P2PKH],   // p2pkh in p2sh (typically multisig).  normally in xpub instead.
            'y'  => [ScriptType::P2SH, ScriptType::P2WKH],
            'Y'  => [ScriptType::P2SH, ScriptType::P2WSH, ScriptType::P2PKH],
            'z'  => [ScriptType::P2WKH],
            'Z'  => [ScriptType::P2WSH, ScriptType::P2PKH],
        ];
        
        $t[] = $this->v($m['x']) ? [ [$m['x']['private'],$m['x']['public']], $st['x'] ] : null;
        $t[] = $this->v($m['x']) ? [ [$m['x']['private'],$m['x']['public']], $st['X'] ] : null;
        $t[] = $this->v($m['y']) ? [ [$m['y']['private'],$m['y']['public']], $st['y'] ] : null;
        $t[] = $this->v($m['Y']) ? [ [$m['Y']['private'],$m['Y']['public']], $st['Y'] ] : null;
        $t[] = $this->v($m['z']) ? [ [$m['z']['private'],$m['z']['public']], $st['z'] ] : null;
        $t[] = $this->v($m['Z']) ? [ [$m['Z']['private'],$m['Z']['public']], $st['Z'] ] : null;
        
        foreach ($t as $row) {
            if(!$row) {
                continue;
            }
            list ($prefixList, $scriptType) = $row;
            foreach($prefixList as &$val) {
                // Slip132\PrefixRegistry expects 8 byte hex values, without 0x prefix.
                $val = str_replace('0x', '', $val);
            }
            $type = implode("|", $scriptType);
            $map[$type] = $prefixList;
        }
        return $map;
    }
    
    private function v($kt) {
        return Internal::aget($kt , 'private') && $kt['public'];
    }

    /**
     * returns prefix bytes for a given key type.
     *
     * @param string $key_type [x,X,y,z,Y,Z]
     *
     * @return array list of prefix bytes for key type.
     * @throws \CoinParams\Exceptions\ArrayKeyNotFound
     */
    public function prefixBytesByKeyType($key_type) {
        return Internal::aget($this->key_type_map, $key_type);
    }
}
