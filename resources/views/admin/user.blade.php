@extends('layouts.admin')

@section('content')
  <section class="my-10 flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
    <div>
      @php
        $titles = [
          'say hello to the eepy.page admin panel!',
          'welcome to the admin panel',
          'g\'day, mate!',
          'hey there!',
          'welcome!',
          'welcome!',
          'welcome in! ready to go?',
        ];
      @endphp
      <h1 class="text-3xl font-semibold sm:text-4xl">{{ \Illuminate\Support\Arr::random($titles) }}</h1>
    </div>
  </section>

  <h2 class="text-2xl font-semibold">Find an account</h2>
  <x-panel class="grid gap-5 p-6 mt-4">
    <form method="POST" action="{{ route('admin.search') }}" class="grid gap-4">
      @csrf
      <div class="space-y-1.5">
        <label for="search-type" class="block text-xs text-muted">Search by</label>
        <select id="search-type" name="type"
          class="w-full rounded-lg border border-border bg-input px-3 py-2.5 text-foreground outline-none transition focus:border-primary focus:ring-4 focus:ring-primary/20">
          @foreach (['username' => 'Username', 'id' => 'ID', 'domain' => 'Domain', 'email' => 'Email', 'referral' => 'Referral code', 'ips' => 'IP addresses'] as $type => $label)
            <option value="{{ $type }}" @selected(old('type', 'username') === $type)>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <x-input name="term" required placeholder="Search by {{ old('type', 'username') }}"
        value="{{ old('term') }}" />
      <div class="flex flex-wrap items-center justify-between gap-3">
        <x-checkbox name="replace" label="Replace previous results" checked />
        <x-button variant="accent"><x-materialsymbols icon="search" />Search</x-button>
      </div>
    </form>
  </x-panel>

  <div class="mt-12 mb-4 flex items-center justify-between gap-4">
    <h2 class="text-2xl font-semibold">Manage users</h2>
    <span class="text-xs text-muted">{{ count($users) . ' ' . str('user')->plural(count($users)) }} loaded</span>
  </div>
  @if (!$users)
    <x-panel class="p-10 text-center text-muted">No account loaded yet. Start with a username search.</x-panel>
  @endif

  <div class="grid gap-5">
    @foreach (array_reverse($users) as $user)
      @php
        $domains = $user['domains'] ?? [];
        $permissions = $user['permissions'] ?? [];
        $admin = $permissions['admin'] ?? [];
        $adminPermissions = $admin['permissions'] ?? [];
        $limits = $permissions['limits'] ?? [];
        $sessions = $user['sessions'] ?? [];
        $ownedTlds = $user['owned-tlds'] ?? [];
        $country = is_array($user['country'] ?? null) ? $user['country'] : [];
      @endphp
      <article class="overflow-hidden rounded-2xl border border-border bg-card shadow-xl shadow-black/10">
        <div
          class="flex flex-col justify-between gap-4 border-b border-border p-5 sm:flex-row sm:items-start sm:px-6">
          <div>
            <div class="text-xl font-semibold {{ $user['banned'] ?? false ? 'text-destructive' : '' }}">
              @if (!empty(data_get($country, 'country_flag.emoji')))
                <span
                  class="mr-1 inline-block align-[-0.5px]"
                  role="img"
                  aria-label="{{ $country['country_name'] ?? 'Country' }} flag"
                  title="{{ $country['country_name'] ?? 'Unknown country' }}"
                >
                  {{ data_get($country, 'country_flag.emoji', '') }}
                </span>
              @endif
              {{ $user['username'] ?? 'Unknown user' }}
              @if ($user['banned'] ?? false)
                <span
                  class="ml-2 rounded bg-alert px-2 py-1 font-mono text-[10px] uppercase text-alert-foreground">banned</span>
              @endif
            </div>
            <div class="mt-1 break-all font-mono text-xs text-muted">{{ $user['id'] ?? '' }}</div>
            <div class="mt-3 flex flex-wrap gap-2">
              <form id="full-admin-{{ $user['id'] ?? '' }}" method="POST" action="{{ route('admin.action') }}">
                @csrf<input type="hidden" name="name" value="full-admin"><input type="hidden"
                  name="user_id" value="{{ $user['id'] ?? '' }}"><x-confirm-modal
                  id="full-admin-modal-{{ $user['id'] ?? '' }}"
                  form="full-admin-{{ $user['id'] ?? '' }}"
                  title="Grant full admin access"
                  confirm-label="Grant access"
                >
                  <x-slot:trigger><x-button type="button" variant="danger" size="small"><x-materialsymbols icon="add-moderator" size="16px" />Grant full admin</x-button></x-slot:trigger>
                  Are you sure you would like to grant unrestricted admin access to this user?
                </x-confirm-modal>
              </form>
              <form id="manual-login-{{ $user['id'] ?? '' }}" method="POST" action="{{ route('admin.action') }}">
                @csrf<input type="hidden" name="name" value="manual-login"><input type="hidden"
                  name="user_id" value="{{ $user['id'] ?? '' }}"><x-confirm-modal
                  id="manual-login-modal-{{ $user['id'] ?? '' }}"
                  form="manual-login-{{ $user['id'] ?? '' }}"
                  title="Create manual login"
                  confirm-label="Proceed with session creation"
                  confirm-variant="danger"
                >
                  <x-slot:trigger>
                    <x-button type="button" variant="ghost" size="small">
                      <x-materialsymbols icon="login" size="16px" />
                      Manual login
                    </x-button>
                  </x-slot:trigger>
                  Are you <strong>100% positive</strong> that you would like to <strong>create a manual login session</strong> for this user?
                  <br><br>
                  This tool can be <strong>extremely dangerous if used improperly</strong>, as it allows <strong>direct access to a user's account</strong> without consent.
                  <br><br>
                  To adhere to the <strong>eepy.page terms of service and privacy policies</strong>, you must ensure that this action is </strong>performed responsibly</strong> and <strong>only when absolutely necessary</strong>.
                  <br><br>
                  Some examples of allowed uses:
                  <ul class="list-disc pl-6">
                    <li>Recovering an account</li>
                    <li>Development and debugging &lpar;on an account specifically made for testing&rpar;</li>
                    <li>Assisting a user with their account</li>
                  </ul>
                  However, even in these cases, this should be purely used as a last resort.
                  <br><br>
                  If you would like to continue, press the button below.
                </x-confirm-modal>
              </form>
              @if (!($user['verified'] ?? false))
                <form id="verify-{{ $user['id'] ?? '' }}" method="POST" action="{{ route('admin.action') }}">
                  @csrf
                  <input type="hidden" name="name" value="verify">
                  <input type="hidden" name="user_id" value="{{ $user['id'] ?? '' }}">
                  <x-confirm-modal
                    id="verify-modal-{{ $user['id'] ?? '' }}"
                    form="verify-{{ $user['id'] ?? '' }}"
                    title="Force verify account"
                    confirm-label="Verify account"
                    confirm-variant="danger"
                  >
                    <x-slot:trigger><x-button type="button" variant="ghost" size="small"><x-materialsymbols icon="check" size="16px" />Force verify</x-button></x-slot:trigger>
                    <p class="break-normal!">
                      This option is showing because <strong>this user has not verified their account</strong>.
                      <br><br>
                      They currently <strong>do not have access</strong> until they are verified; whether by manual means or through the email sent to their inbox.
                      <br><br>
                      Are you <strong>100% positive</strong> that you would like to <strong>manually mark this user's account as verified</strong>?
                      <br><br>
                      Since manually verifying a user bypasses the email verification, this adds the risk of the user getting locked out of their account if they do not have access to the email they signed up with.
                    </p>
                  </x-confirm-modal>
                </form>
              @endif
            </div>
          </div>
          @if ($user['banned'] ?? false)
            <form id="reinstate-{{ $user['id'] ?? '' }}" method="POST" action="{{ route('admin.action') }}" class="flex flex-wrap items-center gap-3">
              @csrf
              <input type="hidden" name="name" value="reinstate">
              <input type="hidden" name="user_id" value="{{ $user['id'] ?? '' }}">
              <x-confirm-modal
                id="reinstate-modal-{{ $user['id'] ?? '' }}"
                form="reinstate-{{ $user['id'] ?? '' }}"
                title="Reinstate account"
                confirm-label="Reinstate account"
                confirm-variant="accent"
              >
                <x-slot:trigger><x-button type="button" variant="accent">Reinstate account</x-button></x-slot:trigger>
                Are you sure you would like to reinstate this user's account?
                <x-checkbox form="reinstate-{{ $user['id'] ?? '' }}" name="send_email" label="Send email" containerClass="mt-3" />
              </x-confirm-modal>
            </form>
          @endif
        </div>
        <div class="grid gap-6 p-5 sm:p-6">
          <dl class="grid gap-3 sm:grid-cols-2">
            <x-stat title="Email">{{ $user['email'] ?? 'Unavailable' }}</x-stat>
            <x-stat title="Created">{{ !empty($user['created']) ? date('M j, Y H:i T', $user['created']) : 'Unknown' }}</x-stat>
            <x-stat title="Last login">{{ !empty($user['last_login']) ? date('M j, Y H:i T', $user['last_login']) : 'Never' }}</x-stat>
            <x-stat title="Beta enrolled">{{ $user['beta-enroll'] ?? false ? 'Yes' : 'No' }}</x-stat>
            <x-stat title="Domains">{{ count($domains) }}</x-stat>
            <x-stat title="Active sessions">{{ count($sessions) }}</x-stat>
            <x-stat title="API keys">{{ $user['api_key_amount'] ?? 0 }}</x-stat>
            <x-stat title="Account verified">{{ ($user['verified'] ?? false) ? 'Yes' : 'No' }}</x-stat>
          </dl>
          <details class="border-t border-border pt-4">
            <summary class="cursor-pointer font-semibold">Location and access history</summary>
            <div class="mt-4 grid gap-4 text-sm">
              <div class="flex flex-col gap-2">
                <h3 class="font-semibold">
                  Location <span class="text-muted">&lpar;at signup&rpar;</span>
                </h3>
                <dl class="grid gap-2 sm:grid-cols-2">
                  <x-detail label="Country" :value="($country['country_name'] ?? ($country['country'] ?? 'Unknown')) . (!empty($country['country']) ? ' (' . $country['country'] . ')' : '')" />
                  <x-detail label="City and region" :value="($country['city'] ?? 'Unknown') . (!empty($country['region']) ? ', ' . $country['region'] : '')" />
                  <x-detail label="Network" :value="$country['org'] ?? 'Unknown'" />
                  <x-detail label="Timezone" :value="$country['timezone'] ?? 'Unknown'" />
                  <x-detail label="Coordinates" :value="$country['loc'] ?? 'Unknown'" mono />
                  <x-detail label="Postal code" :value="$country['postal'] ?? 'Unknown'" mono />
                </dl>
              </div>
              <div>
                <h3 class="font-semibold">Recorded access IPs</h3>
                <div class="mt-1 font-mono text-sm text-muted">{{ implode(', ', $user['accessed_from'] ?? []) }}</div>
              </div>
              <form method="POST" action="{{ route('admin.search') }}">
                @csrf
                <input type="hidden" name="type" value="ips">
                <input type="hidden" name="term" value="{{ implode("\n", $user['accessed_from'] ?? []) }}">
                <x-button variant="ghost" size="small">Find these IPs</x-button>
              </form>
            </div>
          </details>
          <details class="border-t border-border pt-4">
            <summary class="cursor-pointer font-semibold">Sessions ({{ count($sessions) }})</summary>
            <div class="mt-3 divide-y divide-border">
              @forelse($sessions as $session)
                <x-session-row :session="$session" />
              @empty
                <span class="text-sm text-muted">No active sessions.</span>
              @endforelse
            </div>
          </details>

          <section class="flex flex-row gap-3 border-t border-border pt-4 [&_.divider]:mx-1 [&_.divider]:w-[0.5px] [&_.divider]:bg-border">
            <div class="grid gap-3 border-border h-min w-1/3">
              <h3 class="font-mono text-xs tracking-wider text-muted mt-0.5 -mb-0.5">Administrator</h3>
              @php
                $adminPermissionNames = [
                  'account',
                  'dns',
                  'manage-permissions',
                  'reports',
                  'userdetails',
                  'wildcards',
                ];
                $adminEnabled = ($admin['enabled'] ?? false) === true;
              @endphp
              <form id="permissions-{{ $user['id'] ?? '' }}" method="POST" action="{{ route('admin.action') }}" class="grid gap-3">
                @csrf
                <input type="hidden" name="name" value="permissions"><input type="hidden"
                  name="user_id" value="{{ $user['id'] ?? '' }}">
                <div class="overflow-hidden rounded-lg border border-border">
                  <table class="w-full text-sm">
                    <thead class="border-b border-border bg-secondary/30 text-xs text-muted">
                      <tr>
                        <th class="px-3 py-2 text-left">Permission</th>
                        <th class="w-20 px-3 py-2 text-right">Enabled?</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                      <tr class="border-border border-b-2">
                        <td class="px-3 py-2 font-mono text-xs text-muted">
                          administrator
                        </td>
                        <td class="px-3 py-2 text-right">
                          <input type="hidden" name="permissions[enabled]" value="false">
                          <x-checkbox
                            id="admin-enabled-{{ $user['id'] ?? '' }}"
                            name="permissions[enabled]"
                            value="true"
                            :checked="$adminEnabled"
                            class="admin-enabled h-3.5 w-3.5"
                            data-permissions="admin-permissions-{{ $user['id'] ?? '' }}"
                            aria-controls="admin-permissions-{{ $user['id'] ?? '' }}"
                            aria-expanded="{{ $adminEnabled ? 'true' : 'false' }}"
                          />
                        </td>
                      </tr>
                      <tr id="admin-permissions-{{ $user['id'] ?? '' }}"
                        class="{{ $adminEnabled ? '' : 'hidden' }}">
                        <td colspan="2" class="p-0">
                          <table class="w-full">
                            <tbody class="divide-y divide-border bg-secondary/10">
                              @foreach ($adminPermissionNames as $permission)
                                <tr>
                                  <td class="px-3 py-2 font-mono text-xs text-muted">
                                    {{ $permission }}</td>
                                  <td class="px-3 py-2 w-20 text-right">
                                    <input type="hidden"
                                      name="permissions[{{ $permission }}]"
                                      value="false">
                                    <x-checkbox
                                      id="{{ $permission }}-{{ $user['id'] ?? '' }}"
                                      name="permissions[{{ $permission }}]"
                                      value="true"
                                      :checked="($adminPermissions[$permission] ?? false) === true"
                                      class="h-3.5 w-3.5"
                                    />
                                  </td>
                                </tr>
                              @endforeach
                            </tbody>
                          </table>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <x-confirm-modal
                  id="permissions-modal-{{ $user['id'] ?? '' }}"
                  form="permissions-{{ $user['id'] ?? '' }}"
                  title="Update administrator permissions"
                  confirm-label="Apply changes"
                  confirm-variant="accent"
                >
                  <x-slot:trigger><x-button type="button" variant="accent" size="small"><x-materialsymbols icon="check" size="16px" />Apply changes</x-button></x-slot:trigger>
                  Are you sure you would like to update this user's administrator permissions?
                  <x-checkbox form="permissions-{{ $user['id'] ?? '' }}" name="send_email" label="Send email" containerClass="mt-3" />
                </x-confirm-modal>
              </form>
              <script>
                document.querySelector('[data-permissions="admin-permissions-{{ $user['id'] ?? '' }}"]')?.addEventListener(
                  'change',
                  function(event) {
                    const checkbox = event.currentTarget;
                    const permissions = document.getElementById(checkbox.dataset.permissions);
                    permissions?.classList.toggle('hidden', !checkbox.checked);
                    checkbox.setAttribute('aria-expanded', checkbox.checked ? 'true' : 'false');
                  });
              </script>
            </div>
            <div class="divider"></div>
            <div class="grid gap-3 border-border h-min w-1/3">
              <h3 class="font-mono text-xs tracking-wider text-muted mt-0.5 -mb-0.5">Account limits</h3>
              @php
                $readableMap = [
                  'max-domains' => 'Max Domains',
                  'max-subdomains' => 'Max Subdomains',
                ]
              @endphp
              @foreach ($limits as $limit => $value)
                <form id="permission-{{ $user['id'] ?? '' }}-{{ $limit }}"
                  method="POST"
                  action="{{ route('admin.action') }}"
                  class="grid gap-2 sm:grid-cols-[minmax(100px,.8fr)_1fr_auto] sm:items-center"
                >
                  @csrf
                  <input type="hidden" name="name" value="permission">
                  <input type="hidden" name="user_id" value="{{ $user['id'] ?? '' }}">
                  <input type="hidden" name="permission" value="{{ $limit }}">
                  <span class="font-mono text-xs text-muted">{{ $readableMap[$limit] ?? $limit }}</span>
                  <x-input
                    class="[&>input]:px-2.5 [&>input]:py-2 [&>input]:text-xs"
                    name="value"
                    value="{{ is_scalar($value) ? $value : json_encode($value) }}"
                  />
                  <x-confirm-modal
                    id="permission-modal-{{ $user['id'] ?? '' }}-{{ $limit }}"
                    form="permission-{{ $user['id'] ?? '' }}-{{ $limit }}"
                    title="Update account limits"
                    confirm-label="Update"
                    confirm-variant="ghost"
                  >
                    <x-slot:trigger>
                      <x-button type="button" variant="ghost" size="small">
                        <x-materialsymbols icon="check" size="16px" />
                        Update
                      </x-button>
                    </x-slot:trigger>
                    Are you sure you would like to change this user's account limits?
                    <x-checkbox form="permission-{{ $user['id'] ?? '' }}-{{ $limit }}" name="send_email" label="Send email" containerClass="mt-3" />
                  </x-confirm-modal>
                </form>
              @endforeach
            </div>
            <div class="divider"></div>
            <div class="grid gap-3 border-border h-min w-1/3">
              <h3 class="font-mono text-xs tracking-wider text-muted mt-0.5 -mb-0.5">TLDs</h3>
              @foreach ($tlds ?? [] as $tld)
                <form id="tld-{{ $user['id'] ?? '' }}-{{ str_replace('.', '-', $tld) }}"
                  method="POST"
                  action="{{ route('admin.action') }}"
                  class="flex items-center h-min justify-between gap-3"
                >
                  <span>.{{ $tld }}</span>

                  @php
                    $owned = in_array($tld, $ownedTlds, true);
                  @endphp

                  <input type="hidden" name="name" value="{{ $owned ? 'remove-tld' : 'add-tld' }}">
                  <input type="hidden" name="user_id" value="{{ $user['id'] ?? '' }}">
                  <input type="hidden" name="tld" value="{{ $tld }}">
                  <x-confirm-modal
                    id="tld-modal-{{ $user['id'] ?? '' }}-{{ str_replace('.', '-', $tld) }}"
                    form="tld-{{ $user['id'] ?? '' }}-{{ str_replace('.', '-', $tld) }}"
                    title="{{ $owned ? 'Remove TLD' : 'Add TLD' }}"
                    confirm-label="{{ $owned ? 'Remove' : 'Add' }}"
                    confirm-variant="{{ $owned ? 'danger' : 'ghost' }}"
                  >
                    <x-slot:trigger>
                      <x-button
                        type="button"
                        variant="{{ $owned ? 'danger' : 'ghost' }}"
                        size="small"
                      >
                        {{-- <x-materialsymbols icon="{{ $owned ? 'block' : 'add-circle' }}" size="16px" /> --}}
                        <x-materialsymbols icon="{{ $owned ? 'remove' : 'add' }}" size="16px" />
                        {{ $owned ? 'Remove' : 'Add' }}
                      </x-button>
                    </x-slot:trigger>

                    Are you sure you would like to {{ $owned ? 'remove' : 'add' }} the <span class="font-mono">.{{ $tld }}</span> TLD {{ $owned ? 'from' : 'to' }} this user's account?

                    <x-checkbox
                      form="tld-{{ $user['id'] ?? '' }}-{{ str_replace('.', '-', $tld) }}"
                      name="send_email"
                      label="Send email"
                      containerClass="mt-3"
                    />
                  </x-confirm-modal>
                </form>
              @endforeach
            </div>
          </section>

          <section class="grid gap-3 border-t border-border pt-4">
            <h3 class="font-mono text-xs tracking-wider text-muted">Domains</h3>
            @forelse($domains as $domain)
              <form id="delete-domain-{{ $user['id'] ?? '' }}-{{ md5($domain['name'] ?? '') }}" method="POST" action="{{ route('admin.action') }}"
                class="grid gap-2 sm:grid-cols-[1fr_auto] sm:items-end">
                @csrf
                <input type="hidden" name="name" value="delete-domain">
                <input type="hidden" name="user_id" value="{{ $user['id'] ?? '' }}">
                <input type="hidden" name="domain" value="{{ $domain['name'] ?? '' }}">
                <input type="hidden" name="type" value="{{ $domain['type'] ?? 'A' }}">
                @php
                  $domainId = \App\Helpers\Helpers::slugifyDomainForURL($domain['name']);
                @endphp
                <div id="{{ $domainId }}" class="space-y-2">
                  <strong>{{ $domain['name'] ?? 'Unknown' }}</strong>
                  <p class="font-mono text-[13px] text-muted">
                    Type: {{ $domain['type'] ?? '' }}<br>
                    Value: {{ is_array($domain['ip'] ?? null) ? implode(', ', $domain['ip']) : $domain['ip'] ?? '' }}
                  </p>
                  <x-button
                    type="button"
                    variant="ghost"
                    size="small"
                    onclick="window.location.href = '{{ route('admin.domain.detail', [
                      'userId' => $user['id'] ?? '',
                      'domain' => $domain['name'] ?? '',
                      'type' => $domain['type'] ?? null
                    ]) }}'"
                  >
                    <x-materialsymbols icon="edit" size="16px" />
                    Edit domain
                  </x-button>
                  <x-input name="reason" class="[&>input]:px-2.5 [&>input]:py-2 [&>input]:text-xs" placeholder="Reason for deletion" />
                </div>
                <x-confirm-modal
                  id="delete-domain-modal-{{ $user['id'] ?? '' }}-{{ md5($domain['name'] ?? '') }}"
                  form="delete-domain-{{ $user['id'] ?? '' }}-{{ md5($domain['name'] ?? '') }}"
                  title="Delete domain"
                  confirm-label="Delete domain"
                >
                  <x-slot:trigger>
                    <x-button type="button" variant="danger" size="small">
                      <x-materialsymbols icon="delete" size="16px" />
                      Delete domain
                    </x-button>
                  </x-slot:trigger>
                  Are you sure you would like to delete <span class="font-mono">{{ $domain['name'] ?? 'this domain' }}</span>?
                  <x-checkbox form="delete-domain-{{ $user['id'] ?? '' }}-{{ md5($domain['name'] ?? '') }}" name="send_email" label="Send email" containerClass="mt-3" />
                </x-confirm-modal>
              </form>
            @empty
              <span class="text-sm text-muted">No domains registered.</span>
            @endforelse
          </section>
          @if (!($user['banned'] ?? false))
            <details class="border-t border-border pt-4">
              <summary class="cursor-pointer font-semibold">Terminate account</summary>
              <form id="delete-account-{{ $user['id'] ?? '' }}" method="POST" action="{{ route('admin.action') }}" class="mt-4 space-y-3">@csrf<input
                  type="hidden" name="name" value="delete-account"><input type="hidden"
                  name="user_id" value="{{ $user['id'] ?? '' }}"><label
                  for="reasons-{{ $user['id'] }}" class="block text-xs text-muted">Reasons, one per
                  line</label>
                <textarea id="reasons-{{ $user['id'] }}" name="reasons" required placeholder="Reason one&#10;Reason two"
                  class="min-h-24 w-full resize-y rounded-lg border border-border bg-input px-3 py-2.5 text-foreground outline-none focus:border-primary focus:ring-4 focus:ring-primary/20"></textarea><x-confirm-modal
                  id="delete-account-modal-{{ $user['id'] ?? '' }}"
                  form="delete-account-{{ $user['id'] ?? '' }}"
                  title="Terminate account"
                  confirm-label="Terminate account"
                >
                  <x-slot:trigger>
                    <x-button type="button" variant="danger" size="small">
                      <x-materialsymbols icon="delete-forever" size="16px" />
                      Confirm account termination
                    </x-button>
                  </x-slot:trigger>
                  Are you sure you would like to terminate this account? This action cannot be undone.
                  <x-checkbox form="delete-account-{{ $user['id'] ?? '' }}" name="send_email" label="Send email" containerClass="mt-3" />
                </x-confirm-modal>
              </form>
            </details>
          @endif
        </div>
      </article>
    @endforeach
  </div>
@endsection
