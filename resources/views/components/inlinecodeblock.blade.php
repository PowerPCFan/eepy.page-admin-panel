<code
  class="font-mono text-xs border border-border rounded-sm bg-[#0f0f0f] text-foreground px-0.5 py-px cursor-pointer"
  onclick="navigator.clipboard.writeText('{{ $slot }}').then(() => { alert('Copied to clipboard!'); }).catch((err) => { console.error('Failed to copy text: ', err); });"
>{{ $slot }}</code>
