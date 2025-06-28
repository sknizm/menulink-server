<?php
namespace Deployer;

require 'recipe/laravel.php';   // or whichever recipe you use

// … your existing hosts, git repo, etc.

// ⬇︎  Put it anywhere *after* the recipe is loaded
set('shared_dirs', [
    'public/uploads',          // <‑‑ this is the new, persistent folder
]);

// If you already had other shared dirs, just append the new one:
//
// set('shared_dirs', array_merge(
//     get('shared_dirs'),
//     ['public/uploads']
// ));
//
// ─ or simply add 'public/uploads' to the existing list.

// … the rest of deploy.php (hosts, tasks, etc.)
