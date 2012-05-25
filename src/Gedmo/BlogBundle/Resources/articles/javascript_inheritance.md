# Using prototypal inheritance in javascript

When coding full stack applications in javascript or just a component library, you
soon realize how it is important to keep it extensible and maintainable. This article
will mainly introduce you, how to use a prototypal inheritance method to apply OOP
style in javascript and how to do it well.

Main context:

- Private variables, methods
- Prototype extension
- Overriding methods
- Runtime method overriding
- Global scope methods
- Self invoking functions

[blog_reference]: http://gediminasm.org/article/using-prototypal-inheritance-in-javascript "How to apply OOP style in javascript and make it extensible, strict and dynamic"

**Note:**

- Last update date: **2010-08-16**

## Setup environment

Hey theres nothing much here, just create **index.html** and **person.js** and lets simple:

``` html
<!-- index.html file -->
<!DOCTYPE HTML>
<html>
<head>
  <title>Prototypal inheritance</title>
  <script type="text/javascript" src="person.js"></script>
</head>
<body>

<script type="text/javascript">
(function() {
    // some code
}());
</script>

</body>
</html>
```

First thing to note, the function in declared in such a fancy style, self contains the code. Etc it
will be accessable and visible only in that scope. It does not map to window directly, use cases
will be shown later.

Now lets start with a common class definition of **person**. I'll comment on code
the portions which may be useful to understand better

``` js
// file: person.js
// note its the same as writting: window.Person = ... it is in global - window scope
/**
 * A function definition is like a constructor everything defined in it
 * will be run inside when object is created
 */
var Person = function(name) {
    /**
     * This is a private variable, only accessible in the constructor
     */
    var _id = Person.identity++;

    /**
     * Public variable which can be accessed or assigned
     * in the constructor or reasigned to initialized object
     */
    this.name = name;

    /**
     * Another public var
     */
    this.surname = '';

    /**
     * This is a private function only
     * usable in constructor scope
     */
    function valid(str) {
        return (/[\a]+/).test(str);
    };

    /**
     * This method can be publically executed
     * but cannot be overrided, neither in extended classes.
     * It can return and use private variables
     */
    this.getId = function() {
        return _id;
    };

    /**
     * The public method in constructor is useful
     * to strictly define the functionality because it cannot be overriden,
     * which may use other public or private variables, methods to fulfill it.
     */
    this.print = function() {
        if (!valid(this.fullname)) {
            throw "Sorry cannot print the person, it must have a fullname";
        }
        console.log(_id + ' --> ' + this.fullname());
    };
};

/**
 * This definition is like a statically accessable variable of Person
 * Does not change when new one is created.
 */
Person.identity = 0;

/**
 * This method is using Person prototype and can be
 * extended/overrided in child classes, also it can
 * be changed on initialized object
 */
Person.prototype.fullname = function() {
    return (this.surname.length ? this.surname + ' ' : '') + this.name;
};

/**
 * Another prototype method
 */
Person.prototype.greet = function() {
    console.log('Hello, my name is ' + this.fullname());
};
```

Now lets play around a bit to see how this works:

``` html
<!-- modify index.html script block -->
<script type="text/javascript">
(function() {
    // create two persons
    var tom = new Person('Tom');
    var jane = new Person('Jane');
    jane.surname = 'Doe';

    tom.print();  // outputs: 0 --> Tom
    jane.print(); // outputs: 1 --> Doe Jane

    // prototype extension methods can be overrided runtime
    tom.fullname = function() {
        return '';
    };
    try {
        tom.print(); // will throw an exception
    } catch (err) {
        console.log('Caught exception: ' + err);
    }
    tom._id === undefined && console.log('_id is unaccessable'); // its private and unaccessible
}());
</script>
```

## Extending the Person

Here is an example of Employee which extends person.

``` js
// file: employee.js
var Employee = function(name, occupation) {
   /**
     * This is a very important part. It invokes Person constructor
     * by applying arguments to Person constructor which runs in the
     * scope Employee context through "this" variable as first argument
     *
     * You can read more about "apply" or "call" from the official sources
     *
     * Note: we run it early in constructor so that it initializes the public
     * variables and methods.
     */
    Person.apply(this, arguments);

    /**
     * A public variable
     */
    this.occupation = occupation;
};

/**
 * These two lines are very important. Usage of "new" operand is also necessary
 * so that prototypal methods could be inherited or extended. We basically use
 * a prototype of parent but construct it as child
 */
Employee.prototype = new Person;
Employee.prototype.constructor = Employee;

/**
 * In this case we extend the fullname method in Employee as a child class
 * Note: to invoke parent method we call it with context of our Employee object
 */
Employee.prototype.fullname = function() {
    return Person.prototype.fullname.call(this) + " and I'm " + this.occupation;
};
```

Now lets include it in **index.html** and see how inheritance works

``` html
<head>
  <script type="text/javascript" src="person.js"></script>
  <script type="text/javascript" src="employee.js"></script>
</head>

```

And add some more js code in our code block index.html

``` js
var john = new Employee('John', 'developer');
john.surname = 'Doe';
var rose = new Employee('Rose', 'receptionist');

rose.greet(); // outputs: Hello, my name is Rose and I'm receptionist
jane.greet(); // outputs: Hello, my name is Doe Jane
john.greet = function() {
    Person.prototype.greet.call(this);
    console.log('And I love Jane');
};
john.greet(); // outputs: Hello, my name is Doe John and I'm developer \n And I love Jane
```

**NOTE:** concerning interfaces in javascript. There is no at least readable way as far as I know
to make such. I would advice to avoid trying implementing it.

## Self invoking functions

Note that **Person.identity** accessible publically and can be reset, which is not acceptable.
Now is a good time to show why self invoking functions are useful and what is their meaning.
These functions are interpreted and executed while javascript is parsed by browser. It incubates
everything inside in a limited scope.

Lets rewrite our **Person** class in a different way.

``` js
// file: person.js

var Person = (function() {

    /**
     * This variable is accessable only in this scope
     */
    var identity = 0;

    /**
     * We can name our person class short, since its in the
     * self contained scope and return it later as a class
     */
    var p = function(name) {
        /**
         * This is a private variable, only accessible in the constructor
         */
        var _id = identity++;

        /**
         * Public variable which can be accessed or assigned
         * in the constructor or reasigned to initialized object
         */
        this.name = name;

        /**
         * Another public var
         */
        this.surname = '';

        /**
         * This is a private function only
         * usable in constructor scope
         */
        function valid(str) {
            return (/[\a]+/).test(str);
        };

        /**
         * This method can be publically executed
         * but cannot be overrided, neither in extended classes.
         * It can return and use private variables
         */
        this.getId = function() {
            return _id;
        };

        /**
         * The public method in constructor is useful
         * to strictly define the functionality because it cannot be overriden,
         * which may use other public or private variables, methods to fulfill it.
         */
        this.print = function() {
            if (!valid(this.fullname)) {
                throw "Sorry cannot print the person, it must have a fullname";
            }
            console.log(_id + ' --> ' + this.fullname());
        };
    }; // end of constructor

    /**
     * This method is using Person prototype and can be
     * extended/overrided in child classes, also it can
     * be changed on initialized object
     */
    p.prototype.fullname = function() {
        return (this.surname.length ? this.surname + ' ' : '') + this.name;
    };

    /**
     * Another prototype method
     */
    p.prototype.greet = function() {
        console.log('Hello, my name is ' + this.fullname());
    };

    // return our person class/function
    return p;
}());
```

Now in application runtime you will not be able to modify or reset **identity** generator.
In self invoking functions you can add methods and anything what can help you with calculations
for the object, which will not be exposed for public access.

Further more, self invoking functions can take parameters, etc you can give anything as a parameter
to be used as something in the scope of that function.

``` js
var obj = {};
(function(o) {
    o.name = 'foo';
}(obj));
console.log('Something happened to: ' + obj.name + ' object');
```

The actions can be described like: self invoking function takes **obj** to be used as **o**

## Recursion in javascript

To call function recursively in javascript you need to use **arguments.callee**
Its a reference to the same function you are operating in. See an example on how
to sum object values recursively:

``` js
var foo = {
    a: 1,
    b: 2,
    c: {
        a: 3,
        b: {
            a: 4
        }
    }
};

function sum(obj) {
    var result = 0;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) {
            if (typeof obj[key] == 'object') {
                result += arguments.callee(obj[key]);
            } else {
                result += obj[key];
            }
        }
    }
    return result;
}

console.log('Recursive sum of obj values: ' + sum(foo));
```

## apply and call methods

These are very important and hard to understand in javascript, if you come from different language.
But it is very meaningful and useful to use in inheritance and in more advanced behavioral
components.

What it does, is invokes the given function in a context of something given. Etc. it can be object
which can be used as a reference for some kind of calculations. But its hard to explain in words.
Lets start from most basic examples. Like adding a name to the object:

``` js
function setProperty(name) {
    this.prop = name;
}

var foo = {};
var bar = {};
setProperty.call(foo, "look mom I'm foo");
setProperty.apply(bar, ["here it goes to bar"]);

console.log(foo, bar);
```

This is how it works. **apply** is different in a way that it takes arguments as array, **call** as
a parameter group. Using apply like we did for **Employee** we passed all arguments which function
may be given, even if we expect only one so far.

Using the inheritance model shawn in this example, to invoke parent methods it is mandatory to use
**call** or **apply**, you can think of many examples on where it may become useful.

## Some tips

Sometimes it may look handy to extend native javascript objects like **Array** with some meaningful
functions. But please take my advice: **don't** if you want someone to use and respect your code.
Like someone said "theres a special place in hell for those, who extends native object prototypes".
Few reasons why its such a big deal:

- If you put your **magic** code in some application, which is using some javascript it may hang
somewhere deep, because it may also extend natives.
- Debuging of such issues is insane process.
- Noone likes to put such code in their applications, especially libraries.

Hope you have learned something interesting. Someday later I will try to create a post on how to use
jquery widgets in real javascript applications in meaningful maintainable way.

