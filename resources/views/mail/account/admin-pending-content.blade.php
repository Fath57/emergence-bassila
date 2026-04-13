<p>Équipe Bassila Emergence,</p>

<p>Le membre <strong>{{ $user->first_name }} {{ $user->last_name }}</strong> ({{ $user->email }}) a confirmé sa suppression. Il a publié <strong>{{ $postCount }}</strong> article(s) publié(s) sur la plateforme.</p>

<p>Purge planifiée&nbsp;: <strong>{{ $purgeAt->isoFormat('D MMMM YYYY') }}</strong>.</p>

<p>Les articles seront anonymisés automatiquement. Vérifiez en amont si une action éditoriale spécifique (dépublication, retitrage) est nécessaire&nbsp;:</p>
<p><a href="{{ $adminUrl }}">Tableau des suppressions</a></p>
