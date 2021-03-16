<div>
    @include('users.partials.header', [
        'title' => __('Hello') . ' '. auth()->user()->name,
        'description' => __('This is your profile page. Here you can modify your user information, password, and generate new authentication tokens for API access.'),
        'class' => 'col-lg-7'
    ])
</div>
