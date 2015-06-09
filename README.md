# Introduction
Easy integration testing for IndieCert. Determine if your server is configured
correctly and everything is working as expected.

# Running
There are two scripts, `run_authn.php` and `run_authz.php` that can be used
to test the two flows of IndieAuth: authentication and authorization.

Configure the scripts at the bottom by setting the relevant parameters.

## Authentication
First you have to decide on a home page that contains a client certificate
fingerprint. The included public key has the following fingerprint. You can
for example place this on `https://localhost/`.

    <link rel="publickey" href="ni:///sha-256;PyZcDB-vGYhFswBBa1kT7wHyctDFKvdvYLrcKTftVg8?ct=application/x-x509-user-cert">

Now you configure the instance of IndieCert you want to test:

    $i = new IndieCertTest('https://indiecert.example', 'https://localhost/');

Here you indicate you want to test `https://indiecert.example` with the
home page `https://localhost/`.

Now run the script. If all is well you should only see `DONE` on the screen:

    $ php run_authn.php 
    DONE

## Authorization
Set up the home page as mentioned in the Authentication section above. Now
for configuring the script, you need two more fields. A 'Bearer' token that
can be used to verify the `access_token` at the introspection endpoint.

You can obtain this by logging in to your IndieCert instance and generating a
credential on the `/account` page.

    $i = new IndieCertTest(
        'https://indiecert.example',
        'https://localhost/',
        '419d8105c2b318fa9943bb46c9f3ceac',
        'post'
    );

The credential here is `419d8105c2b318fa9943bb46c9f3ceac` and the scope 
requested is `post`.

You can also run this script like the authentication script and it should have
the same output:

    $ php run_authz.php 
    DONE

That is all!
