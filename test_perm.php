<?php
require 'vendor/autoload.php';
\ = require_once 'bootstrap/app.php';
\ = \->make(Illuminate\Contracts\Console\Kernel::class);
\->bootstrap();
app(Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
\ = Spatie\Permission\Models\Permission::findOrCreate('dashboard.view', 'web');
var_dump(\->id, \->name, \->guard_name);

