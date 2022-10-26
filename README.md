
# Container

___

The MulerTech dependency injection container

___

## Installation

###### _Two methods to install Application package with composer :_

1.
Add to your "**composer.json**" file into require section :

```
"mulertech/container": "^1.0"
```

and run the command :

```
php composer.phar update
```

2.
Run the command :

```
php composer.phar require mulertech/container "^1.0"
```

___

## Usage

<br>

###### _Initialize the container (you can give the definitions into this construct) :_

```
$container = new Container();
or
$container = new Container($definitions);
```
```
//definitions file :
return [
new \MulerTech\Container\Definition(\Psr\Http\Server\RequestHandlerInterface::class, \MulerTech\Application\RequestHandler::class),
new \MulerTech\Container\Definition(\MulerTech\Form\Validators\MaxLengthValidator::class, null, [], true)
];
```

<br>

###### _Get simple class :_

```
$container->get(Foo::class);
```

<br>

###### _Add a class with its interface (you can get this class with the interface class name) :_

```
$container->add(FooInterface::class, Foo::class);

$fooClass = $container->get(FooInterface::class);
```

<br>

###### _Add a class with arguments :_

```
$container->add(FooInterface::class, Foo::class, ['argumentName' => 'argument value']);
```

<br>

###### _Set parameters and get it :_

```
$container->setParameter('parameter', 'a value');

echo $container->getParameter('parameter'); // a value
```

<br>

###### _Use parameters into class construct arguments :_

```
$container->setParameter('parameter', 'a value');

$container->add(FooInterface::class, Foo::class, ['argumentName' => '%parameter%']);
```

<br>

###### _Load yaml files parameters into container :_

```
$container = new Container();
$loader = new Loader();
$loader
    ->setFileList(['./path_to_files/file1.yaml', './path_to_files/file2.yaml'])
    ->setLoader(YamlLoader::class)
    ->loadParameters($container);
```

<br>

###### _Set parameters with env value :_

```
#env file :
key=value
```

```
$container = new Container();
$container->setParameter('parameter', 'env(key)');
echo $container->getParameter('parameter'); // value
```


