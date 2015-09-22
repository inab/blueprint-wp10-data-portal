BLUEPRINT WP10 data portal
==========

As this project is based on CakePHP, although the deployment process is straightforward, it has several pre-requisites (derived from this [blog entry](https://rupinderjeetkaur.wordpress.com/2014/08/25/install-cakephp-in-ubuntu-14-04/)).

1. You need both PHP5 (or later) and Apache 2.4. Apache 2.2 could work, but it is untested and the setup blocks could need some compatibility changes.
2. Also, you need to install and enable Apache module [mpm-itk](http://mpm-itk.sesse.net/), which allows specific virtual host queries to be managed under separate user setups. If you are using Ubuntu, package [libapache2-mpm-itk](http://packages.ubuntu.com/trusty/libapache2-mpm-itk) is available.

    Once the module has been setup, if the user ``blueprint`` is holding the deployment of this code, the configuration of the virtual host which is going to run the WP10 data portal needs this instruction (change it accordingly):

    ```apache
  <IfModule mpm_itk_module>
          AssignUserId blueprint blueprint
  </IfModule>
    ```
3. Then, if the deployment directory is ``/home/blueprint/document_root/WP10``, the next setup block must be added to the virtual host configuration block:

    ```apache
    <Directory "/home/blueprint/document_root/WP10">
            Options Indexes FollowSymlinks

            DirectoryIndex index.php
            AllowOverride All
            Require all granted
    </Directory>
    ```
