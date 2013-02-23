<?php

namespace Tyrell;

use Tonic\Resource,
    Tonic\Response;

/**
 * @uri /
 */
class Welcome extends Resource
{
    /**
     * @method GET
     */
    public function welcomeMessage()
    {
        $body = <<<END
<!doctype html>
<title>Welcome</title>
<h1>Welcome to the Tonic micro-framework library</h1>
<p>If you are seeing this message, then everything is working as expected.</p>
<p>The Tyrell namespace contains some example Resource classes to get you started.</p>

<h2>Hello world - src/Tyrell/Hello.php</h2>
<p><a href="hello">Hello world</a> - Get the default representation of the hello world example</p>
<p><a href="hello.html">Hello HTML</a> - Get the HTML representation</p>
<p><a href="hello.json">Hello JSON</a> - Get the JSON representation</p>
<p><a href="hello.fr">Bonjour</a> - Say hello in French</p>
<p><a href="hello/mars">Hello mars</a> - Say hello to mars</p>
<p><a href="hello/deckard">Deckard</a> - Say hello to Rick Deckard</p>
<p><a href="hello/roy">Roy</a> - Say hello to Roy Batty</p>

<h2>Simple HTTP authentication - src/Tyrell/Secret.php</h2>
<p><a href="secret">Simple HTTP authentication example</a> - use aUser/aPassword to see the secret</p>

<hr>
<p>Make sure you read the <a href="https://github.com/peej/tonic/blob/master/README.markdown">README.markdown</a> file.</p>
<p>If you require a full example, checkout <a href="https://github.com/peej/tonic/tree/example">the "example" branch from the Tonic git repo</a>.</p>
END;
        return new Response(Response::OK, $body, array(
            'content-type' => 'text/html'
        ));
    }

}