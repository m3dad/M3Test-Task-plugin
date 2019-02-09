# M3Task Test plugin
Task plugin, which developed for a challenge.

## Description
This plugin was created for a recruitment challenge for assessing my programming skills. *It can be better, but know it is as it is.*

## Capabilities
What this plugin do? It added to WordPress custom post type **Task**, which have taxonomy **Task types** and custom metaboxes **Date of task start**, **Due date**, and **Priority**.

There is small list of abilities of this plugin:
* Add to Wordpress custom post type **Task**
* Posts of this type have custom taxonomy **Task types**
* Posts of this type have custom metaboxes, where user can set **Start date** (*date* input), **Due Date** (*date* input) and **Priority** (*select* with three options **Low**, **Normal** and **High**), first two field have default value of current date.
* Values from metaboxes of posts displays on the list of Tasks, means to list of Tasks added columns **Date of start**, **Due date** and **Priority**, and last have colored icons, where **Low** - it is gray arrow down icon, **Normal** - green arrow up icon, and **High** - red arrow up icon.
* Metaboxes and post type Task have Gutenberg support ('show_in_rest' solution)

## Install
Just download this plugin, unpack, add it to plugins directory and activate in Wordpress admin panel.
