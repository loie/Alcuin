# Alcuin
####REST API for the rest of us

Alcuin creates the database schema and the corresponding PHP classes to provide a fully working REST API for you.
All it needs, is a JSON configuration file and a working PHP environment with a MySQL database.

###What can Alcuin do fo you?
You want a REST API? You don't like thinking about foreign key constraints in your DB? You don't like facing the problem, that you want an easy way to create a REST service, with user authentification but the initial inertia is too big, so that you use full-stack web application frameworks like Codeigniter. And other, also great frameworks like Laravel need the command like to be installed. But you don't have access to the command line, because you use a simple hosting service.

This is the point where Alcuin steps in and saves the day.

Alcuin creates a REST service for you, without having to fiddle around with command lines statements and works without much technological dependencies.

###How to Start

There are only a few simple steps to take:

1. Download this repostory.
2. Adapt the `configuration.json` to match your settings and wishes
3. Upload the repository (including the edited `configuration.json`) to your webspace.
4. Execute `index.php` on your webspace
5. Have a beer and enjoy life. You have just setup a fully working REST service. You're awesome!


##User Guide
###Permissions:

Alcuin handles user authorization by roles. A role is saved as a row in the tbale, which is specified to be used for permissions. You can specify the roles to be used in your system by defining a table, in addition with the use_for_permission: true statement. If you want, you can also specify pre-filled values by {instances}.

Alcuin will create your PHP code in such manner, that it will expect the strings you specified to either grant or deny access to that specific resource.

In addition to the specified roles in the tabe used for permission, alcuin uses the following labels for different kind of authorization

"all": Everybody with a browser or cURL or the ability to send web requests has access.
"none": Nobody has access.
"self": If the resource is associated to the "user" (which is an entry in the table, that is specified with "use_for_auth" via either "belongs_to", "has_many" or "belongs_to_and_has_many" relations, then the currently authenticated "user" has access to this ressource. If method "create" is chosen, "self" is forbidden, because the newly created resource is not related to the logged in user, but will be created in that way.