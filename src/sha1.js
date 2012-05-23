function sha1(msg)
{
    // constants
    var K = [0x5a827999, 0x6ed9eba1, 0x8f1bbcdc, 0xca62c1d6];

    // PREPROCESSING
    msg += String.fromCharCode(0x80);  // add trailing '1' bit to string

    // convert string msg into 512-bit/16-integer blocks arrays of ints
    var l = Math.ceil(msg.length/4) + 2;  // long enough to contain msg plus 2-word length
    var N = Math.ceil(l/16);              // in N 16-int blocks
    var M = new Array(N);

    for (var i=0; i<N; i++) {
        M[i] = new Array(16);
        for (var j=0; j<16; j++)  // encode 4 chars per integer, big-endian encoding
            M[i][j] = (msg.charCodeAt(i*64+j*4)<<24) | (msg.charCodeAt(i*64+j*4+1)<<16) | (msg.charCodeAt(i*64+j*4+2)<<8) | (msg.charCodeAt(i*64+j*4+3));
        // note running off the end of msg is ok 'cos bitwise ops on NaN return 0
    }

    // add length (in bits) into final pair of 32-bit integers (big-endian)
    // note: most significant word would be ((len-1)*8 >>> 32, but since JS converts
    // bitwise-op args to 32 bits, we need to simulate this by arithmetic operators
    M[N-1][14] = ((msg.length-1)*8) / Math.pow(2, 32); M[N-1][14] = Math.floor(M[N-1][14])
    M[N-1][15] = ((msg.length-1)*8) & 0xffffffff;

    // set initial hash value
    var H0 = 0x67452301;
    var H1 = 0xefcdab89;
    var H2 = 0x98badcfe;
    var H3 = 0x10325476;
    var H4 = 0xc3d2e1f0;

    // HASH COMPUTATION
    var W = new Array(80);
    var a, b, c, d, e;

    for (var i=0; i<N; i++) {
        // 1 - prepare message schedule 'W'
        for (var t=0;  t<16; t++) W[t] = M[i][t];
        for (var t=16; t<80; t++) {
            W[t] = W[t-3] ^ W[t-8] ^ W[t-14] ^ W[t-16];
            W[t] = (W[t] << 1) | (W[t]>>>31);
        }

        // 2 - initialise five working variables a, b, c, d, e with previous hash value
        a = H0; b = H1; c = H2; d = H3; e = H4;

        // 3 - main loop
        for (var t=0; t<80; t++) {
            var s = Math.floor(t/20); // seq for blocks of 'f' functions and 'K' constants
            var T = ((a<<5) | (a>>>27)) + e + K[s] + W[t];
            switch(s) {
            case 0: T += (b & c) ^ (~b & d); break;          // Ch()
            case 1: T += b ^ c ^ d; break;                   // Parity()
            case 2: T += (b & c) ^ (b & d) ^ (c & d); break; // Maj()
            case 3: T += b ^ c ^ d; break;                   // Parity()
            }
            e = d;
            d = c;
            c = (b << 30) | (b>>>2);
            b = a;
            a = T;
        }

        // 4 - compute the new intermediate hash value
        H0 = (H0+a) & 0xffffffff;  // note 'addition modulo 2^32'
        H1 = (H1+b) & 0xffffffff;
        H2 = (H2+c) & 0xffffffff;
        H3 = (H3+d) & 0xffffffff;
        H4 = (H4+e) & 0xffffffff;
    }

    var hex = "";
    for (var i=7; i>=0; i--) { var v = (H0>>>(i*4)) & 0xf; hex += v.toString(16); }
    for (var i=7; i>=0; i--) { var v = (H1>>>(i*4)) & 0xf; hex += v.toString(16); }
    for (var i=7; i>=0; i--) { var v = (H2>>>(i*4)) & 0xf; hex += v.toString(16); }
    for (var i=7; i>=0; i--) { var v = (H3>>>(i*4)) & 0xf; hex += v.toString(16); }
    for (var i=7; i>=0; i--) { var v = (H4>>>(i*4)) & 0xf; hex += v.toString(16); }
    return hex;
}
