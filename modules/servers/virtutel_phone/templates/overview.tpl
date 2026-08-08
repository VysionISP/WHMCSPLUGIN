<div style="background:#0b0f1a;border:1px solid #2a3347;border-radius:14px;padding:24px;color:#e6e9f2;
            font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Arial,sans-serif">

  <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    {if $vp_state == 'active'}
      <span style="width:14px;height:14px;border-radius:50%;background:#2fbf71;box-shadow:0 0 12px rgba(47,191,113,.8)"></span>
      <div>
        <div style="font-size:20px;font-weight:800">Your phone service is active</div>
        <div style="color:#98a2b8;font-size:13.5px">{if $vp_plan}{$vp_plan}{else}Phone service{/if}</div>
      </div>
    {else}
      <span style="width:14px;height:14px;border-radius:50%;background:#e2564a;box-shadow:0 0 12px rgba(226,86,74,.8)"></span>
      <div>
        <div style="font-size:20px;font-weight:800">Needs attention</div>
        <div style="color:#98a2b8;font-size:13.5px">Status: {$vp_status} — contact us and we'll sort it.</div>
      </div>
    {/if}
    {if $vp_number}
      <div style="margin-left:auto;text-align:right">
        <div style="color:#98a2b8;font-size:11px;letter-spacing:.08em;text-transform:uppercase">Phone Number</div>
        <code style="background:#0a0e18;border:1px solid #2a3347;border-radius:8px;padding:3px 10px;color:#e6e9f2;font-size:16px">{$vp_number}</code>
      </div>
    {/if}
  </div>

  <div style="display:flex;gap:16px;flex-wrap:wrap;margin-top:18px">
    <div style="flex:1 1 280px;min-width:260px;background:#141b2b;border:1px solid #2a3347;border-radius:12px;padding:18px 20px">
      <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#98a2b8;margin-bottom:10px">Service</div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739">
        <span style="color:#98a2b8">Number</span><strong>{$vp_number|default:'—'}</strong></div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739">
        <span style="color:#98a2b8">Status</span><strong>{$vp_status}</strong></div>
      {if $vp_plan}
      <div style="display:flex;justify-content:space-between;gap:14px;padding:6px 0">
        <span style="color:#98a2b8;white-space:nowrap">Plan</span><strong style="text-align:right">{$vp_plan}</strong></div>
      {/if}
    </div>

    <div style="flex:1 1 280px;min-width:260px;background:#141b2b;border:1px solid #2a3347;border-radius:12px;padding:18px 20px">
      <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#98a2b8;margin-bottom:10px">Need a hand?</div>
      <p style="color:#98a2b8;font-size:13.5px;margin:0 0 12px;line-height:1.6">
        Call quality issues, voicemail setup, or moving your number — we're local and happy to help.</p>
      <a href="submitticket.php" style="display:inline-block;background:linear-gradient(92deg,#4d8dff,#7a5cff);
         color:#fff;font-weight:700;padding:10px 22px;border-radius:8px;text-decoration:none">Open a ticket</a>
    </div>
  </div>
</div>
