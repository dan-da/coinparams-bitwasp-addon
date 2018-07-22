# coinparams-bitwasp-addon

These are addon classes to integrate bitwasp [bitcoin-php](https://github.com/Bit-Wasp/bitcoin-php) with [coinparams](https://github.com/Bit-Wasp/bitcoin-php) for
multicoin functionality.

This library adds two classes:

* *MultiCoinNetwork* extends BitWasp\Bitcoin\Network\Network and provides address prefixes, etc.
* *MultiCoinRegistry* extends BitWasp\Bitcoin\Key\Deterministic\Slip132\PrefixRegistry
and provides xpub/ypub,zpub extended key prefixes.

These classes accept string arguments \[symbol,network\] to automatically
load prefixes from coinparams.json.  The classes can then be used anywhere
that *Network* or *PrefixRegistry* would normally be used.

See the examples and tests directory for usage.

note: Litecoin has irregular extended key prefixes.  See examples/bip39 for details.
