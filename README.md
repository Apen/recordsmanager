recordsmanager : records management in a backend module
=======================================================
>  Add modules to easily manage your records (insert, edit and export in backend/eID) in one place.

## What does it do?

This extension add modules to easily manage your records (insert, edit and export in backend/eID) in one place (with different PID). It respect the TYPO3 framework and use the tceforms to insert/edit the records.
See the screenshots to have an idea of this extension.

Do not hesitate to contact me if you have any good ideas.

This extension work with TYPO3 6.2.x-7.6.x.

## Screenshots

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/module.png)

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/add.png)

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/edit.png)

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/export.png)



## Settings

### Enable or disable a module

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/enabledisable.png)

In the extension manager you can enable or disable the modules. By default, you will see all the modules:

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/module.png)

### Create a configuration

Next you have to create some configuration (add, edit, export or export eID) to add some items in the module. This configuration records can only be placed on the root page (PID=0).

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/configlist.png)

Important notice:

This extension respect the rights defined in TYPO3. It is necessary to configure the tables/fields of this extension in respect of the rights defined in the “Access” module for a BE user/group.

## Create an "Add" configuration

First, on the root page, add a configuration record of the type "Add":

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/create-add.png)

In this form, you can configure:

* The title of the configuration
* The type (add, edit, export or export eID)
* The table
* Fields to display in the insert form (in respect with the Access module)
* Additional PID where insert some records
* Enable the choose PID function
* Filter by be_groups

The results of this configuration is in the next screenshot:

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/add.png)

By default, the extension list all the folder where records of your table are. And you can add some PID. Next the form are a typical tceform with the fields.

## Create an "Edit" configuration

First, on the root page, add a configuration record of the type "Edit":

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/create-edit.png)

In this form, you can configure:

* The title of the configuration
* The type (add, edit, export or export eID)
* The table
* Fields to display in the list view
* Fields to display in the form (in respect with the Access module)
* Extra WHERE, GROUP BY, ORDER BY and LIMIT to filter the SQL request
* Filter by group

The results of this configuration is in the next screenshot:

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/edit.png)

Next form are a typical tceform with the fields.

## Create an "Export" configuration

First, on the root page, add a configuration record of the type "Export":

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/create-export.png)

In this form, you can configure :
* The title of the configuration
* The type (add, edit, export or export eID)
* The export mode (XML, CSV or EXCEL)
* The table
* Fields to display in the list view
* Extra WHERE, GROUP BY, ORDER BY and LIMIT to filter the SQL request
* The filter field that allow you to filter on a timestamp field (default tstamp)
* Enable/Disable date fields in short format
* Filter by group
* Field list (separated by ,) not converted by the TCA (example: you can display categories uid instead of label, or you can display timestamp instead of formated date)

The results of this configuration is in the next screenshot:

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/export.png)

## Create an "Export eID" configuration

> This kind of configuration allow you to generate a JSON feed according to a TCA table.

First, on the root page, add a configuration record of the type "Export eID":

![](https://raw.githubusercontent.com/Apen/recordsmanager/master/Resources/Public/Images/create-eid.png)

In this form, you can configure :
* The title of the configuration
* The type (add, edit, export or export eID)
* The table
* The eID key (necessary to access the url)
* Fields to display in the JSON feed
* Extra WHERE, GROUP BY, ORDER BY and LIMIT to filter the SQL request
* Enable/Disable date fields in short format
* Field list (separated by ,) not converted by the TCA (example: you can display categories uid instead of label, or you can display timestamp instead of formated date)
* Extra typoscript code (this section allow a lot of tricky stuff to manipulate datas in your feed). You can use any typoscript function.

Examples of extra typoscript code:

```
lang = CASE
lang.key.data = field:sys_language_uid
lang.0 = TEXT
lang.0.value = fr
lang.1 = TEXT
lang.1.value = en

date = TEXT
date.data = field:datetime
date.strftime = %Y/%m/%d

heure = TEXT
heure.data = field:datetime
heure.strftime = %H:%M

discipline = TEXT
discipline {
    value = ###CATEGORY###
    split {
      token = ;
      wrap = | ,|*||*| |
    }
}
discipline.wrap = [|]

lieu = TEXT
lieu.data = field:tx_sacparisnews_lieu
lieu.wrap = [|]

link = TEXT
link.typolink {
  parameter = 169
  additionalParams = &tx_ttnews[tt_news]={field:uid}
  additionalParams.insertData = 1
  returnLast = url
  forceAbsoluteUrl = 1
}
```

Now you can acces to your JSON at this URL:
http://www.example.com/index.php?eID=recordsmanager&eidkey=xxx&format=json

There is also some extra GET parameter that are interesting:
* format : json OR excel (default)
* pid : allow to specify a PID in the url (it add a SQL query “AND pid=xxx”)

> "Export" and "Export eID" feature support "powermail" 1.x formated results (XML).


