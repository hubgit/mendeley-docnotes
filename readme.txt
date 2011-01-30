Licensed under the MIT license: http://www.opensource.org/licenses/mit-license.php
HumanNameParser by Jason Priem at https://github.com/jasonpriem/HumanNameParser.php under the MIT license: http://www.opensource.org/licenses/mit-license.php

Setting up citeproc-node:
http://www.zotero.org/support/dev/citeproc-node

On 32-bit OS X you need to build node-o3-xml:
git clone http://github.com/ajaxorg/o3
cd o3
./tools/node_modules_build
cp build/default/o3.node ../node-o3-xml/lib/o3-xml/

Copy config.ini.dist to config.ini, and fill in consumer_key and consumer_secret from http://dev.mendeley.com/applications/
Run 'php index.php' at the command line. It should open up an authentication URL in your default web browser. Accept the authorisation, copy the PIN and paste it at the command line prompt. It should then output values for token and token_secret, which you should copy into config.ini.

Now you can open mendeley-docnotes in a web browser. It should fetch a list of your collections, and when you choose one it will fetch the metadata for each document. For every document that has notes, it will output the notes along with a citation generated using citeproc-node.

