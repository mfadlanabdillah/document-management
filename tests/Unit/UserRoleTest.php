<?php

use App\Models\User;

it('can evaluate admin role via helper', function () {
    $admin = new User(['role' => 'admin']);
    $regular = new User(['role' => 'user']);

    expect($admin->isAdmin())->toBeTrue();
    expect($regular->isAdmin())->toBeFalse();
});
