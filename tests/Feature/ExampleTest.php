<?php

test('public dishes api returns a successful response', function () {
    $this->seed();
    $response = $this->getJson('/api/v1/dishes');

    $response->assertStatus(200)
        ->assertJsonPath('success', true);
});
