<!DOCTYPE HTML>
<html lang="en-US">
	<head>
		<meta charset="UTF-8">
		<title></title>
	</head>
	<body>
		<p>Hello,</p>

		<p>The account owned by "{{ $user->first_name }}" has been deactivated or newly created, you should check the details of this user (if required) and handle it appropriately.</p>

		<p>Use this link to view the user's profile:</p>

		<p>----------------------------</p>

		<p>Name: {{ $user->first_name }} {{ $user->last_name }}</p>

		<p>Email: {{ $user->email }}</p>

		<p>----------------------------</p>

		<p>Use this link to activate the account:</p>

		<p><a href="{{ $activationLink }}">{{ $activationLink }}</a></p>

		<p>@setting('platform.site.title')</p>
	</body>
</html>
