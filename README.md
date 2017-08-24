# grpc-fastcgi-example

Example usage of [grpc-fastcgi-proxy](https://github.com/bakins/grpc-fastcgi-proxy)

This is a simple PHP application copied from the [gRPC examples](https://grpc.io/docs/quickstart/php.html) and a client of that example.

## Bootstrap

This has been tested with PHP7 on OS X.  To install the prerequisites using 
homebrew:

```shell
brew tap homebrew/php
brew install php71 --without-apache --with-fpm
brew install homebrew/php/php71-grpc
brew install homebrew/php/php71-protobuf
```

The [gRPC quickstart guide](https://grpc.io/docs/quickstart/php.html) has instructions for Linux.

## Testing

Clone, build, and run [grpc-fastcgi-proxy](https://github.com/bakins/grpc-fastcgi-proxy).

It should use `index.php` in the repository for it's script file. So run something like:

```shell
grpc-fastcgi-proxy $HOME/git/grpc-fastcgi-example/index.php
```

Start php-fpm. On OS X, `/usr/local/sbin/php71-fpm start`

Now run `php ./greeter_client.php` (make sure you are using PHP 71).

```shell
$ php greeter_client.php
Hello world
```

That's it.  It doesn't look like much, but this sent a gRPC request to `grpc-fastcgi-proxy`
which proxied it to php-fpm and handled all the transcoding.

## LICENSE

See [LICENSE](./LICENSE).

## Acknowledgements

- The PHP code is based on the [gRPC examples](https://github.com/grpc/grpc/tree/master/examples/php).

