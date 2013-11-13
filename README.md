fwlogview
=========

A complete firewall inspection utility.

This project aims to be a simple and fast / low resource way to view firewall relevant information. Primarily the three area's I have found important are IPTable logging for past intrusion attempts, Conntrack to view current connections and the ARP table to view physically connected devices. There are not many firewall log viewers that have been updated/maintained in the past 5 years and I wanted something that was compatible with the latest ulog2 process.

Feel free to log issues and submit pull requests on the fwlogview github page.

If you like this software or find it useful please consider donating to my bitcoin address below.

Bitcoin: 14zYqNfs6ubktnLkqCCmQdYA8qmGMXA9L5

If you are interested in paid support or sponsored development for this software, contact me via my email listed on github.

Install
=======
Install ulog2 and create the database tables using the ulog2 provided scripts.  Make sure iptables is writing logs into the table.

Copy the repository into your Web root or into a subfolder.

Copy the config.php.sample to config.php and fill in your DB settings.

You should be good to go.

WARNINGS
=========
This is still very much Alpha quality software, do now allow public access to this website.
