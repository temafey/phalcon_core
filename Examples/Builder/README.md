Example Instruction
===================

This is an example of builder.

First step. In your test database create tables for testing. Tables sql you can find in builder.sql file.

Second step. Go to config/config.php and configure database connection credentials.

Third step. Go to Examples/Builder folder and run in terminal buildModel.php for building models

Like this:
~~~~~bash
php buildModel.php
~~~~~

Run in terminal buildForm.php for building forms

Like this:
~~~~~bash
php buildForm.php
~~~~~

Or run buildGrid.php for building grids

Like this:
~~~~~bash
php buildGrid.php
~~~~~

Also you can generate all together. Just run buildScaffold.php

Like this:
~~~~~bash
php buildScaffold.php
~~~~~