# IOC Container -- Inversion of Control Container
Facilitating Interface Oriented Design

## Getting Started

### Prerequisites
<ul>
  <li>PHP >= 5.4</li>
  <li>"bonzer/exceptions" : "dev-master"</li>
</ul>

### Installing 
It can be installed via composer. Run
```
composer require bonzer/ioc-container
```

## Usage
```
use Bonzer\IOC_Container\facades\Container as App_Container;
```

### Binding to Container
There are two methods available:  
<code>bind</code> or <code>singleton</code> for binding your class with container.<br> 
used as:
```
App_Container::bind('My_Class', 'My\Namespace\My_Class');
App_Container::singleton('My_Class', 'My\Namespace\My_Class');
```
#### Binding Interfaces:
You can also bind your interfaces with container as:
```
App_Container::bind('My\Namespace\My_Interface', 'My\Namespace\My_Class');
```
and whenever you use this interfaces in any other class and if that class is bound and/or instantiated via <code>IOC_Container</code>
the interface will automatically gets resolved.

### Instantiating
For instantiating, Just call <code>App_Container::make</code> with the bound key:
```
App_Container::make('My_Class');
```
* <b>Note</b>: If you have bound with <code>singleton</code>, <code>App_Container::make</code> will return the same instance everytime.

#### Direct Instantiation:
Direct Instantiation is also possible, without binding. For eg.
```
App_Container::make('My\Namespace\My_Class');
```
for singleton instantiation without binding, use:
```
App_Container::make_singleton('My\Namespace\My_Class');
```

* <b>Note</b>: If your class has private constructor, then <code>IOC_Container</code> checks for the availability of any of the two static methods: <code>init</code> or <code>get_instance</code>

## Support
If you are having issues, please let me know.<br>
You can contact me at ralhan.paras@gmail.com

## License
The project is licensed under the MIT license.