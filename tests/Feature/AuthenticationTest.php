<?php

test('unauthenticated user redirect to login when access products') 
    ->get('/products/all')
    ->assertStatus(302);

