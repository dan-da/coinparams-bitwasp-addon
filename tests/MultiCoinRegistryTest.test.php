<?php

namespace tester;

require_once __DIR__  . '/tests_common.php';

use CoinParams\BitWasp\MultiCoinRegistry;

class MultiCoinRegistryTest extends test_base {
    
    public function runtests() {
        $this->btc_mainnet();
        $this->dgb_mainnet_with_btc_defaults();
        $this->dgb_mainnet_without_btc_defaults();
    }
    
    protected function btc_mainnet() {
        $chain = 'BTC-main';
        $mcr = new MultiCoinRegistry('BTC');
        
        $x = $mcr->prefixBytesByKeyType('x');
        $this->eq( @$x['public'], '0x0488b21e', "$chain xpub prefix bytes" );
        $this->eq( @$x['private'], '0x0488ade4', "$chain xprv prefix bytes" );

        $X = $mcr->prefixBytesByKeyType('X');
        $this->eq( @$X['public'], '0x0488b21e', "$chain xpub prefix bytes (p2sh)" );
        $this->eq( @$X['private'], '0x0488ade4', "$chain xprv prefix bytes (p2sh)" );
        
        $y = $mcr->prefixBytesByKeyType('y');
        $this->eq( @$y['public'], '0x049d7cb2', "$chain ypub prefix bytes" );
        $this->eq( @$y['private'], '0x049d7878', "$chain yprv prefix bytes" );

        $Y = $mcr->prefixBytesByKeyType('Y');
        $this->eq( @$Y['public'], '0x0295b43f', "$chain Ypub prefix bytes" );
        $this->eq( @$Y['private'], '0x0295b005', "$chain Yprv prefix bytes" );
        
        $z = $mcr->prefixBytesByKeyType('z');
        $this->eq( @$z['public'], '0x04b24746', "$chain zpub prefix bytes" );
        $this->eq( @$z['private'], '0x04b2430c', "$chain zprv prefix bytes" );

        $Z = $mcr->prefixBytesByKeyType('Z');
        $this->eq( @$Z['public'], '0x02aa7ed3', "$chain Zpub prefix bytes" );
        $this->eq( @$Z['private'], '0x02aa7a99', "$chain Zprv prefix bytes" );
    }
    
    protected function dgb_mainnet_with_btc_defaults() {
        $chain = 'DBG-main';
        $mcr = new MultiCoinRegistry('DGB', 'main', ['undefined_used_btc' => true]);
        
        $x = $mcr->prefixBytesByKeyType('x');
        $this->eq( @$x['public'], '0x0488b21e', "$chain xpub prefix bytes" );
        $this->eq( @$x['private'], '0x0488ade4', "$chain xprv prefix bytes" );

        $X = $mcr->prefixBytesByKeyType('X');
        $this->eq( @$X['public'], '0x0488b21e', "$chain xpub prefix bytes (p2sh)" );
        $this->eq( @$X['private'], '0x0488ade4', "$chain xprv prefix bytes (p2sh)" );
        
        $y = $mcr->prefixBytesByKeyType('y');
        $this->eq( @$y['public'], '0x049d7cb2', "$chain ypub prefix bytes, btc default" );
        $this->eq( @$y['private'], '0x049d7878', "$chain yprv prefix bytes, btc default" );

        $Y = $mcr->prefixBytesByKeyType('Y');
        $this->eq( @$Y['public'], '0x0295b43f', "$chain Ypub prefix bytes, btc default" );
        $this->eq( @$Y['private'], '0x0295b005', "$chain Yprv prefix bytes, btc default" );
        
        $z = $mcr->prefixBytesByKeyType('z');
        $this->eq( @$z['public'], '0x04b24746', "$chain zpub prefix bytes, btc default" );
        $this->eq( @$z['private'], '0x04b2430c', "$chain zprv prefix bytes, btc default" );

        $Z = $mcr->prefixBytesByKeyType('Z');
        $this->eq( @$Z['public'], '0x02aa7ed3', "$chain Zpub prefix bytes, btc default" );
        $this->eq( @$Z['private'], '0x02aa7a99', "$chain Zprv prefix bytes, btc default" );
    }


    protected function dgb_mainnet_without_btc_defaults() {
        $chain = 'DBG-main';
        $mcr = new MultiCoinRegistry('DGB', 'main');
        
        $x = $mcr->prefixBytesByKeyType('x');
        $this->eq( @$x['public'], '0x0488b21e', "$chain xpub prefix bytes" );
        $this->eq( @$x['private'], '0x0488ade4', "$chain xprv prefix bytes" );

        $X = $mcr->prefixBytesByKeyType('X');
        $this->eq( @$X['public'], '0x0488b21e', "$chain xpub prefix bytes (p2sh)" );
        $this->eq( @$X['private'], '0x0488ade4', "$chain xprv prefix bytes (p2sh)" );
        
        $y = $mcr->prefixBytesByKeyType('y');
        $this->eq( @$y['public'], null, "$chain ypub prefix bytes" );
        $this->eq( @$y['private'], null, "$chain yprv prefix bytes" );

        $Y = $mcr->prefixBytesByKeyType('Y');
        $this->eq( @$Y['public'], null, "$chain Ypub prefix bytes" );
        $this->eq( @$Y['private'], null, "$chain Yprv prefix bytes" );
        
        $z = $mcr->prefixBytesByKeyType('z');
        $this->eq( @$z['public'], null, "$chain zpub prefix bytes" );
        $this->eq( @$z['private'], null, "$chain zprv prefix bytes" );

        $Z = $mcr->prefixBytesByKeyType('Z');
        $this->eq( @$Z['public'], null, "$chain Zpub prefix bytes" );
        $this->eq( @$Z['private'], null, "$chain Zprv prefix bytes" );
    }
    
}
