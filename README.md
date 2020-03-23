Hi there!

So I decided to pick api platform as a base because I wanted to play with it
for quite a long time already, but didn't have a chance until now.


To play locally, you'll need to make Chrome trust your dev CA.
For that build and run the containers as usually by

docker-compose up


then copy the content of the cert:

 docker-compose exec dev-tls cat localCA.crt

to some file and import it as a CA in Chrome.

This little API can register your user and log them in.
User can create and update their own Tasks and cannot see/update Tasks of other Users.
Data comes back in different formats, which you can control with Accept header
(content-type negotiation).

You can see this both from tests, which you can run by

 docker-compose exec php vendor/bin/simple-phpunit

or go to https://localhost:8443/docs to see the documentation and send some requests from there.
Please feel free to ask any questions.
