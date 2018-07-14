# htmlpriv2wif

<p>1 - Take a private key
</p>
<pre>   0C28FCA386C7A227600B2FE50B7CAE11EC86D3BF1FBE471BE89827E19D72AA1D <span style="font-size:0px;">EXAMPLE PRIVATEKEY DO NOT IMPORT</span>
</pre>
<p>2 - Add a 0xa9 byte in front of it for mainnet addresses or 0xee for testnet addresses. //Also add a 0x01 byte at the end if the private key will correspond to a compressed public key//
</p>
<pre>   800C28FCA386C7A227600B2FE50B7CAE11EC86D3BF1FBE471BE89827E19D72AA1D
</pre>
<p>3 - Perform SHA-256 hash on the extended key
</p>
<pre>   8147786C4D15106333BF278D71DADAF1079EF2D2440A4DDE37D747DED5403592
</pre>
<p>4 - Perform SHA-256 hash on result of SHA-256 hash
</p>
<pre>   507A5B8DFED0FC6FE8801743720CEDEC06AA5C6FCA72B07C49964492FB98A714
</pre>
<p>5 - Take the first 4 bytes of the second SHA-256 hash, this is the checksum
</p>
<pre>   507A5B8D
</pre>
<p>6 - Add the 4 checksum bytes from point 5 at the end of the extended key from point 2
</p>
<pre>   800C28FCA386C7A227600B2FE50B7CAE11EC86D3BF1FBE471BE89827E19D72AA1D507A5B8D
</pre>
<p>7 - Convert the result from a byte string into a base58 string using <a href="/wiki/Base58Check_encoding" title="Base58Check encoding">Base58Check encoding</a>. This is the Wallet Import Format
</p>