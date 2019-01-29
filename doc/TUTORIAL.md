# The ATK Book
[TOC]

## What is it? 
ATK is a PHP Framework intended to build business application. ATK has some very high level capabilities for CRUD building, validating inputs and controlling access.
ATK lets yo build a table "admin", a paginated list of rows in a database table, this list comes paired with create, read, update and delete forms with allmost zero coding on your part.
ATK is the right tool if you are writing an application where editing database tables is the main functionallity and design and presentation of individual pages are not a strong concern (Because ATK generates the pages for you automagically).

## A little history
ATK names comes from "Achievo Tool Kit". Achievo was a project planning software written by a Ducth company called iBuildings. ATK was the framework developed to help create the application and was later released as a stand alone tool, hence it's name.
iBuildings created and maintained ATK until 2006 when they stopped supporting it, from that time on, several forks has been made by people wanting to keep it alive.
The guys at Sintattica.it talked to iBuildings founder, Ivo Jansch who handed them the ATK wiki and forum in order to keep those resources online.
Sintatica made several improvements on ATK, moving the version from 6.7 (The last iBuildings release) to ATK 8. But ATK 8 was irremediably old. ATK was written initially with PHP4 and a lot of water has passed under the bridge since ATK first appearence, so the guys at Sinttatica.it decided to go a little further and rebuild ATK with modern PHP and modern tools, this new version is called ATK 9 and makes use of modern PHP object orientation constructs and tools. 
This book will cover how to build applications with ATK 9, this book will not discuss differences with previous version at all, if you are an old ATK user keep in mind that while ATK 9 is "philosophically" similar to older versions, it is not retro compatible and if you want to port an old ATK pre 9 app you will find that some heavy lifting is in order, hopefully, you will also find that it worths the trouble too.

## Let's dive in: Building our first app

Let build's a conference app our app will allow us to register the Speaker, the conference titles, and the conference attendants for each conference.

### Getting the necesary tools.

We will be using a debian based Linux distro in this book.
We will need to have **git** installed, in a debian based Linux distribution it can 
be installed with:

`sudo apt-get install git`


Now we will need **composer.phar** Composer is a tool for dependency management in PHP. It allows you to declare the libraries your project depends on and it will manage (install/update) them for you.
To grab a copy please execute:
`
 php -r "readfile('https://getcomposer.org/installer');" > composer-setup.php 
`

And then, run the setup script wth:
`
./composer-setup.php
`

This will leave a **composer.phar** file in your work directory, now you can get rid of the setup script with :

`rm composer-setup.php`

To simplify typing, rename **composer.phar** to just composer with:

`mv composer.phar composer`

And make sure it is executable with:

`chmod +x composer`

Finally, you should put composer in the path to be readily available when needed, please run:

`sudo mv composer.phar /usr/local/bin/composer`

Now we are gonna need to clone the Sintattica/atk-skeleton project. The skeleton project is an empty project to serve as boiler plate for your own project. In order to grab a copy you will need git:

`
git clone https://github.com/Sintattica/atk-skeleton.git conference
`

This should download a copy of the skeleton project in a directory called **conference**, the directory should have the following structure:

```
conference/
├── atk-skeleton.sql
├── composer.json
├── config
│   ├── app.php
│   ├── atk.php
│   ├── parameters.dev.php
│   ├── parameters.dist.php
│   ├── parameters.prod.php
│   ├── parameters.staging.php
├── README.md
├── languages
│   ├── en.php
│   └── it.php
├── src
│   └── Modules
│       ├── App
│       │   ├── languages
│       │   │   ├── en.php
│       │   │   └── it.php
│       │   ├── Module.php
│       │   └── TestNode.php
│       └── Auth
│           ├── Groups.php
│           ├── languages
│           │   ├── en.php
│           │   └── it.php
│           ├── Module.php
│           ├── UsersGroups.php
│           └── Users.php
├── var
└── web
    ├── bundles
    │   └── atk -> ../../vendor/sintattica/atk/src/Resources/public
    ├── index.php
    └── images
        ├── brand_logo.png
        └── login_logo.png
```

Let's take a quick look to some files and directories:

- composer.json: It is the composer dependencies file, any time you need a new software library you should add its name here and run **composer update**.
- The config direcory contains the configuration files.
- The src directory: Our work will go mainly in this directory, this is the directory where our application sources will reside, more specifically in the modules directory.
- The var directory is for temporary files
- The web directory is the directory that will need to be served by a web server (Apache, Nginx, Lighttpd or any other).

Maybe you have observed that the web/bundles subdirectory is a symbolic link to an inexistent vendor directory, that directory is the directory that composer uses to store the downloaded dependencies and it will be created when composer updates the dependencies, let's do that with:

`
composer update
`

After composer finishes the updating you will have a vendor directory containing all the project dependencies.

### Creating a Database

As we've said, ATK is a business oriented framework and that implies that building CRUD interfaces for SQL Tables is a breeze, then, it is obvious that we are gonna need a Database, ATK has "drivers" for:

- MySQL
- PostGress

In this text we will gonna use MySQL.
Let's create a database called **conference** and grant all privileges to user **conference** with password **conference**.
The above requirment can be achieved by excuting:

`mysql -u  root -p `

And once you are inside the mysql cli prompt issue the following commands:

`create database conference;`

And :

`grant all on conference.* to conference@localhost identified by 'conference';`

If you take a look around the skeleton project maybe you noticed a file called **atk-skeleton.sql** lying in the root directory, this file contains the table definitions for ATK security system, your database should have these tables, we will create them with:

`mysql -u conference -p conference < atk-skeleton.sql `

Now, we will need to configure our application.

### Configuring our application

Main configuration options are specified in **config/** directory. Specifically, the parameters.xxx.php files (where xxx stands for dev, dist, prod or staging) contains per-site variables. For this tutorial, we'll work with "dev" environment so you'll have to specify values in **config/parameters.dev.php**.

Let's take a look at the contents of the file:
```
return [
    'atk' => [

        'identifier' => 'atk-skeleton-dev',

        'db' => [
            'default' => [
                'host' => 'localhost',
                'db' => 'atk-skeleton',
                'user' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'driver' => 'MySqli',
            ],
        ],

        'debug' => 1,
        'meta_caching' => false,
        'auth_ignorepasswordmatch' => false,
        'administratorpassword' => '$2y$10$erDvMUhORJraJyxw9KXKKOn7D1FZNsaiT.g2Rdl/4V6qbkulOjUqi', // administrator
    ],
];

```
It's allready obvious that we need to change db, user and password from 'atk-skeleton'/'root'/'' to our database parameters ('conference'/'conference'/'conference'), but  what about the funny line ADMIN_PASSWORD?
The admin password is the administrative ATK password, when you login into an ATK application with the user **administrator**, all security is bypassed and you can do anything. It is the super user password.
You have to set an administrative password in the **.env** file, but you have to store it encrypted, ATK provides a tool to encrypt the password, and you invoke it like this:

` php ./vendor/sintattica/atk/src/Utils/generatehash.php demo`

The clear password is **demo**, once you run the command you'll get something like:

``` 
clean: demo
hash: $2y$10$HURwCzn3JJmSV.8UZEVW/eaO/RSlYKELKFacIwTyKSPssxp101XDC
```

Let's edit our parameters.dev.php file, to look like this:

```
<?php

return [
    'atk' => [

        'identifier' => 'atk-skeleton-dev',

        'db' => [
            'default' => [
                'host' => 'localhost',
                'db' => 'root',
                'user' => 'conference',
                'password' => 'conference',
                'charset' => 'utf8',
                'driver' => 'MySqli',
            ],
        ],

        'debug' => 1,
        'meta_caching' => false,
        'auth_ignorepasswordmatch' => false,
        'administratorpassword' => '$2y$10$HURwCzn3JJmSV.8UZEVW/eaO/RSlYKELKFacIwTyKSPssxp101XDC', // demo
    ],
];
```

But now that we specified the configuration in parameters.dev.php, how will the server know which parameters file to pick up ? The application will look up to an *environmental variable* called **APP_ENV**. The most simple way to set it is on the command line.

Ok, our basic configuration is done, now we can have a little gratification, let's 
take a look to our app, in order to do so, let's start our personal php web server with:

`APP_ENV=dev php -S 0.0.0.0:8000 -t web/`

Now open your browser and navigate to **http://localhost:8000** you should see a login form. You can now login with user **administrator** and password **demo**.
Most probably, the login form is shown in the italian language (As the Sintattica.it are italians that should come as not surprising), let's tell our app to show up in good old english, edit de **config/atk.php** file and change the line:

`'language' => 'it', `

to

`'language' => 'en',`

Taking a look to **vendor/sintattica/atk/src/Resources/languages/** you should see:

```
bp.php  cf.php  da.php  el.php  es.php  fr.php  id.php  ja.php  no.php  pt.php  sk.php  tr.php  zh.php  ca.php  cs.php  de.php  en.php  fi.php  hu.php  it.php  nl.php  pl.php  ru.php  sv.php  uk.php
```

This is the complete list of languages that atk is translated to, if your language isn't there, copy the **en.php** to your **xx.php**, translate it and add it to the project git.

### Our first Module

Have you checked your application via your browser ? Exploring a bit under authentication menu, you can see that we already have :
- A full authentication system, featuring [Universal second factor](https://en.wikipedia.org/wiki/Universal_2nd_Factor) authentication.
- An ACL management for users and tables.

Nice, isn't it ?

Our first module will consist of the list of conferences, stored as a table in database. Our `app_conference` table will carry title, subtitle and speakers as a VARCHAR field, description as a TEXT field, room as one value among 'Borg', 'Adams' and 'Dijkstra', a start time and a duration. So run `mysql -u conference --database conference -p` and type :
```
CREATE TABLE `app_conference` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `subtitle` varchar(512) DEFAULT NULL,
  `speakers` varchar(200) DEFAULT NULL, -- For the moment, let's juste store speakers in a text field
  `description` text,
  `room` ENUM('Borg', 'Adams', 'Dijkstra') NOT NULL DEFAULT 'Borg', -- We have 3 rooms in the conference center
  `start` timestamp NOT NULL, -- Date/time of the conference
  `duration` tinyint NOT NULL, -- How long it will last (in seconds)
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

You can also simply do `mysql conference -u conference -p < tutorial-data/01_first_module.sql` from [atk-tutorial project](https://github.com/Samuel-BF/atk-tutorial), which holds the code of the final version of this tutorial and sample data.

OK, so now we have the database ready. Let's add a "Conferences" module handling all operations on it. Application modules reside in **src/Modules/App** directory. There's already **Module.php** file, which we'll see few lines later, and **testNode.php**, a small sample module. Rename it **Conference.php** and modify it to fit to `app_conference` structure:

```
class Conference extends Node
{
    function __construct($nodeUri)
    {
        parent::__construct($nodeUri, Node::NF_ADD_LINK | Node::NF_EDITAFTERADD);
        $this->setTable('app_conference');

        $this->add(new Attribute('id', Attribute::AF_AUTOKEY));
        $this->add(new Attribute('title', Attribute::AF_OBLIGATORY));
        $this->add(new Attribute('subtitle'));
        $this->add(new Attribute('speakers'));
        $this->add(new TextAttribute('description'));
        $this->add(new Attribute('room'));
        $this->add(new Attribute('start', Attribute::AF_OBLIGATORY));
        $this->add(new Attribute('duration', Attribute::AF_OBLIGATORY));

        $this->setDescriptorTemplate('[title]');
    }
}
```

So what's in it ? Code is quite explicit :
- **setTable** : defines the name of the table where the data resides for Conference module.
- **Attribute** : defines a field inside the table, with some options that can be set.
- **TextAttribute** : defines a longer field.
- **setDescriptorTemplate** (less obvious): defines the title of the page when viewing or editing a field. Here, it will consist of the 'title' field.

To use it, modify **Module.php** (we're still in **src/Modules/App**) : 

```
class Module extends \Sintattica\Atk\Core\Module
{
    static $module = 'app';

    public function register()
    {
        $this->registerNode('conference', Conference::class, ['admin', 'add', 'edit', 'delete']);
    }

    public function boot()
    {
        $this->addNodeToMenu('Conferences', 'conference', 'admin');
    }
}
```

Here, two functions are used :
- **registerNode** : references the Conference module and defines actions that can be triggered on it. 'admin' is a page listing values and linking to add, edit and delete forms. Another possible action is 'search'.
- **addNodeToMenu** : first argument is title displayed on the menu, second argument is the name of the Module and third argument is the default action when clicking the link on the menu.

What does it look like ? On your application, there's now a 'Conferences' link on the menu which brings you to page listing all conferences registered and allowing to manage them. Try to add, edit or view a conference : it just works. Well, in fact it's not so easy to set 'Room', 'start' and 'duration' fields. Hopefully you can be more specific about the kind of field it is : e-mail, date, IP address, password, ... All field types are listed under [src/Attributes](../src/Attributes/). Here, we'll logically use ListAttribute, DateTimeAttribute and DurationAttribute in **Conference.php**. First, add at the beginning of the script :

```
use Sintattica\Atk\Attributes\DateTimeAttribute;
use Sintattica\Atk\Attributes\DurationAttribute;
use Sintattica\Atk\Attributes\ListAttribute;
```

And also modify their definitions to :

```
        $this->add(new ListAttribute('room', Attribute::AF_OBLIGATORY, ['Borg', 'Adams', 'Dijkstra']));
        $this->add(new DateTimeAttribute('start', Attribute::AF_OBLIGATORY));
        $this->add(new DurationAttribute('duration', Attribute::AF_OBLIGATORY));
```

In ListAttribute constructor, the third argument is as you probably guessed the list of values (and if there is a difference between values stored in the database and values shown in the application, add a fourth argument holding shown values).

Check once again the application : you now have a more friendly edit form and values are correctly shown in admin and view pages. Nice !

Could it be better ? Yes. Let's review available options for Attributes. They are listed at the beginning of [Attribute class](../src/Attributes/Attribute.php) (you also have specific optionsfor each kind of field listed in their respective definition class). Here are some options that I find useful :

- **AF_OBLIGATORY** : it says that there should be a value set for this field.
- **AF_AUTOKEY** : used for hidden primary keys that autoincrement. There's almost always one in each of your Modules.
- **AF_HIDE** : don't show the field.
- **AF_HIDE_(LIST|ADD|EDIT|SELECT|VIEW|SEARCH)** : don't show the field in specific page.
- **AF_SEARCHABLE** : in the admin page, add a form allowing to search items according to this field.

We can now tune a bit more our Conference module, hiding potentially long descriptions from list view and allowing a quick search on title, speakers and room :

```
        $this->add(new Attribute('title', Attribute::AF_OBLIGATORY | Attribute::AF_SEARCHABLE));
        $this->add(new Attribute('subtitle'));
        $this->add(new Attribute('speakers', Attribute::AF_SEARCHABLE));
        $this->add(new TextAttribute('description', Attribute::AF_HIDE_LIST));
        $this->add(new ListAttribute('room', Attribute::AF_OBLIGATORY | Attribute::AF_SEARCHABLE, ['Borg', 'Adams', 'Dijkstra']));
```

And that's it! In very few lines, you already have a nice micro-application for managing conferences.
 
## Let's dive further: Adding a Relation				

As you know, the R in [RDBMS](https://en.wikipedia.org/wiki/Relational_database_management_system) stands for "Relational" : tables are in relations with each other to store structured data. So let's add a bit of complexity here, adding a "room" table to list them rather than hard-coding the value in the conference structure. We'll also add a capacity field to this table, telling how much people can attend to a conference in each room.

For this part, we need a app_room table in the database and to modify app_conference to reference IDs in app_room. So let's run this against the database :

```
DROP TABLE IF EXISTS `app_room`;
CREATE TABLE `app_room` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `capacity` smallint unsigned NOT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE app_conference CHANGE room_id smallint NOT NULL;
``

Like in previous section, you can use [tutorial-data/02_relations.sql](https://github.com/Samuel-BF/atk-tutorial/blob/master/tutorial-data/02_relations) from [atk-tutorial project](https://github.com/Samuel-BF/atk-tutorial) which also contains some sample data.

Now that we have the app_room table, let's add a page to manage this data. As you already know, it's quite straightforward. Add a file **Room.php** in **src/Modules/App** with :

```
<?php
namespace App\Modules\App;

use Sintattica\Atk\Core\Node;
use Sintattica\Atk\Attributes\Attribute;
use Sintattica\Atk\Attributes\NumberAttribute;

class Room extends Node
{
    function __construct($nodeUri)
    {
        parent::__construct($nodeUri, Node::NF_ADD_LINK | Node::NF_EDITAFTERADD);
        $this->setTable('app_room');

        $this->add(new Attribute('id', Attribute::AF_AUTOKEY));
        $this->add(new Attribute('name', Attribute::AF_OBLIGATORY));
        $this->add(new NumberAttribute('capacity'));

        $this->setDescriptorTemplate('[name]');
    }
}
```

You notice here that we used the more specific 'NumberAttribute' from the framework. Then, just reference this module in **src/Modules/App/Module.php** adding these lines in corresponding functions :

```
[in register():]
        $this->registerNode('room', Room::class, ['admin', 'add', 'edit', 'delete']);
[...in boot():]
        $this->addNodeToMenu('Rooms', 'room', 'admin');
```

Nothing new here. Now, the relation can be added. In Conference class, replace `use Sintattica\Atk\Attributes\ListAttribute;` by `use Sintattica\Atk\Relations\ManyToOneRelation;` and the line defining room attribute by :

```
        $this->add(new ManyToOneRelation('room', Attribute::AF_OBLIGATORY | Attribute::AF_SEARCHABLE | ManyToOneRelation::AF_RELATION_AUTOLINK, 'app.room'));
```

What is this ? Let's split the line :

- **ManyToOneRelation** : is just one kind of relation. All relation types are listed in [src/Relations](../src/Relations/). A relation is an attribute (Relation class extends Attribute class) that links modules together. And a ManyToOneRelation is a relation where nodes of Class A each have a reference to a node of Class B, and many A-nodes can link to the same B-node. Here, several Conferences can take place in the same room.
- Options : a Relation being an Attribute, you can use common Attribute options, but also per-relation specific options. Here, AF_RELATION_AUTOLINK add a link from the conference node to the room (the link is on the title of the room).
- 'app.room' : this is the target Class of object linked here, in the form **section.node**, where **Section** is the directory under **Modules** and **Node.php** is a file under **Section**

That's it. If you go to "Conferences" page on your webapplication, you can see the column "room" values linking to rooms in their respective view page.

Is it possible to add the list of the conferences taking place in a specific room in the room view page ? It would be a little bit redundant, because it's already possible to filter conferences by room in the "Conferences" page, but yes, it's possible with a OneToMany relation. In **src/Modules/App/Room.php**, just add these to lines :

```
use Sintattica\Atk\Relations\OneToManyRelation;
[...]
        $this->add(new OneToManyRelation('conferences', Attribute::AF_HIDE_LIST, 'app.conference', 'room'));
```

The OneToManyRelation works quite like ManyToOneRelation, but you also have to specify the column in the target database table that references the ID of current Node. Here, in app_conference, that's the room column that holds the room id. The option AF_HIDE_LIST, as exposed earlier, doesn't show the conferences list in the page listing all rooms. They appears on the specific room view page or on edit pages.

## Speakers and conferences : a many-to-many relation

An obvious improvement of the data structure we've put in place is to reference speakers on a separate database. So let's do it with two more tables (or just use [tutorial-data/03_manytomany.sql](https://github.com/Samuel-BF/atk-tutorial/blob/master/tutorial-data/02_relations) which comes with sample data) :

```
DROP TABLE IF EXISTS `app_speaker`;
CREATE TABLE `app_speaker` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `URL` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `app_conference_speaker̀`;
CREATE TABLE `app_conference_speaker` (
 `speaker_id` int unsigned NOT NULL,
 `conference_id` int unsigned NOT NULL,
  PRIMARY KEY (`speaker_id`, `conference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `app_conference` DROP `speakers`;
```

The 'app_conference_speaker' glue table is needed because each conference may have several speakers and each speaker may talk in several conferences. We need a similar glue node in **src/Modules/App/**, let's call it **Conference_Speaker** :

```
<?php

namespace App\Modules\app;

use Sintattica\Atk\Attributes\Attribute;
use Sintattica\Atk\Core\Node;
use Sintattica\Atk\Relations\ManyToOneRelation;

class Conference_Speaker extends Node
{
    function __construct($nodeUri)
    {
        parent::__construct($nodeUri);
        $this->setTable('app_conference_speaker');

        $this->add(new ManyToOneRelation('speaker_id', Attribute::AF_PRIMARY, 'app.speaker'));
        $this->add(new ManyToOneRelation('conference_id', Attribute::AF_PRIMARY, 'app.conference'));
    }
}
```

This glue node must be referenced in **src/Modules/App/Module.php** :

```
        $this->registerNode('conference_speaker', Conference_Speaker::class);
```

But here, there is no need to add an item in the menu corresponding to this node. Then, define the Speaker node type in **src/Modules/App/Speaker.php** :

```
<?php

namespace App\Modules\App;

use Sintattica\Atk\Core\Node;
use Sintattica\Atk\Attributes\Attribute;
use Sintattica\Atk\Attributes\UrlAttribute;

class Speaker extends Node
{
    function __construct($nodeUri)
    {
        parent::__construct($nodeUri, Node::NF_ADD_LINK | Node::NF_EDITAFTERADD);
        $this->setTable('app_speaker');

        $this->add(new Attribute('id', Attribute::AF_AUTOKEY));
        $this->add(new Attribute('name', Attribute::AF_OBLIGATORY));
        $this->add(new Attribute('description', Attribute::AF_HIDE_LIST));
        $this->add(new UrlAttribute('URL', UrlAttribute::AF_POPUP));

        $this->setDescriptorTemplate('[name]');
    }
}
```

And reference it in **src/Modules/App/Module.php** :
```
[in register():]
        $this->registerNode('speaker', Speaker::class, ['admin', 'add', 'edit', 'delete']);
[...in boot():]
        $this->addNodeToMenu('Speakers', 'speaker', 'admin');
```

Nothing new for the moment, except the UrlAttribute which will holds ... well, you guess.

Now it's time to reference speakers in the Conference node definition : remove the previous 'speakers' attribute definition and add :

```
use Sintattica\Atk\Relations\ManyToManySelectRelation;
[...]
        $this->add(new ManyToManySelectRelation('speakers', Attribute::AF_SEARCHABLE | ManyToManyRelation::AF_MANYTOMANY_DETAILVIEW, 'app.conference_speaker', 'app.speaker'));
```

There's more arguments than previously. Let's treat these arguments one by one :

- **ManyToManySelectRelation** is one kind of many-to-many relation. When you edit a conference, you find speakers by typing few letters in a field and it prints you matching speakers.
- **speakers** is the name of the column. Here, it doesn't correspond to a field in the database (in contrast to previous arguments).
- **AF_MANYTOMANY_DETAILVIEW** adds a link on speaker names to the speaker page (similar to AF_RELATION_AUTOLINK in ManyToOneRelation).
- **app.conference_speaker** is the glue node class defined in src/Modules/App/Module.php
- **app.speaker** is the target node class.

And that's it. You can test and try to add speakers to listed conferences. It works as expected. I also encourage you to test other types of many-to-many relations (ManyBoolRelation, ManyToManyListRelation and ShuttleRelation) : they define how you select target nodes when editing a conference node.

As an exercise, you can add the list of conferences a speaker gives in the speaker view page. It takes only 2 more lines.

 *this is work in progress *
