{if $vt_error}
  <div class="alert alert-warning">{$vt_error}</div>
{elseif !$vt_linked}
  <div class="alert alert-info">Your NBN service is being set up — details will appear here shortly.</div>
{else}

{* ---- status hero ---- *}
<div style="background:#141b2b;border:1px solid #2a3347;border-radius:16px;padding:22px 24px;margin-bottom:18px;color:#e6e9f2">
  <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap">
    {if $vt_conn_state == 'active'}
      <span style="width:14px;height:14px;border-radius:50%;background:#2fbf71;box-shadow:0 0 12px rgba(47,191,113,.8)"></span>
      <div>
        <div style="font-size:20px;font-weight:800">Your NBN is active</div>
        <div style="color:#98a2b8;font-size:13.5px">{$vt_technology|default:'NBN'}{if $vt_speed} &middot; {$vt_speed}{/if}</div>
      </div>
    {elseif $vt_conn_state == 'in_progress'}
      <span style="width:14px;height:14px;border-radius:50%;background:#e2a336;box-shadow:0 0 12px rgba(226,163,54,.8)"></span>
      <div>
        <div style="font-size:20px;font-weight:800">We're getting you connected</div>
        <div style="color:#98a2b8;font-size:13.5px">{$vt_order_status_label|default:'Order in progress'}</div>
      </div>
    {else}
      <span style="width:14px;height:14px;border-radius:50%;background:#e2564a;box-shadow:0 0 12px rgba(226,86,74,.8)"></span>
      <div>
        <div style="font-size:20px;font-weight:800">Needs attention</div>
        <div style="color:#98a2b8;font-size:13.5px">Something's not right with this service — contact us and we'll sort it.</div>
      </div>
    {/if}
    {if $vt_avc}
      <div style="margin-left:auto;text-align:right">
        <div style="color:#98a2b8;font-size:11px;letter-spacing:.08em;text-transform:uppercase">AVC ID</div>
        <code style="background:#0a0e18;border:1px solid #2a3347;border-radius:8px;padding:3px 10px;color:#e6e9f2">{$vt_avc}</code>
      </div>
    {/if}
  </div>

  {* ---- provisioning timeline ---- *}
  {if $vt_steps}
    <div style="display:flex;gap:0;margin-top:22px;flex-wrap:wrap">
      {foreach from=$vt_steps item=vtStep name=stepLoop}
        <div style="flex:1;min-width:130px;text-align:center;position:relative">
          {if !$smarty.foreach.stepLoop.first}
            <div style="position:absolute;top:14px;left:-50%;width:100%;height:2px;background:#2a3347;z-index:0"></div>
          {/if}
          <div style="position:relative;z-index:1;width:30px;height:30px;border-radius:50%;margin:0 auto 8px;
                      display:flex;align-items:center;justify-content:center;font-weight:800;font-size:13px;
            {if $vtStep.state == 'done'}background:#2fbf71;color:#fff
            {elseif $vtStep.state == 'current'}background:linear-gradient(92deg,#4d8dff,#7a5cff);color:#fff;box-shadow:0 0 14px rgba(77,141,255,.55)
            {else}background:#1e2739;color:#667;border:1px solid #2a3347{/if}">
            {if $vtStep.state == 'done'}&#10003;{else}{$smarty.foreach.stepLoop.iteration}{/if}
          </div>
          <div style="font-size:13px;font-weight:{if $vtStep.state == 'current'}700{else}600{/if};
                      color:{if $vtStep.state == 'todo'}#667{else}#e6e9f2{/if}">{$vtStep.label}</div>
          {if $vtStep.note}<div style="font-size:11.5px;color:#98a2b8;margin-top:2px">{$vtStep.note}</div>{/if}
        </div>
      {/foreach}
    </div>
  {/if}
</div>

{* ---- appointment booking prompt ---- *}
{if $vt_needs_booking}
  <div style="background:rgba(226,163,54,.1);border:1px solid rgba(226,163,54,.4);border-radius:12px;padding:16px 20px;margin-bottom:18px;color:#e6e9f2">
    <strong>{if $vt_is_reschedule}Your installation appointment needs to be rebooked.{else}Your NBN installation needs an appointment.{/if}</strong>
    Pick a time that suits you — it only takes a minute.
    <p style="margin:10px 0 0">
      <a href="{$vt_booking_url}" class="btn btn-primary">
        {if $vt_is_reschedule}Choose a new time{else}Book your appointment{/if}
      </a>
    </p>
  </div>
{/if}

{* ---- details ---- *}
<div class="row">
  <div class="col-sm-6">
    <div style="background:#141b2b;border:1px solid #2a3347;border-radius:12px;padding:18px 20px;margin-bottom:18px;color:#e6e9f2">
      <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#98a2b8;margin-bottom:10px">Connection</div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739">
        <span style="color:#98a2b8">Technology</span><strong>{$vt_technology|default:'—'}</strong></div>
      <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739">
        <span style="color:#98a2b8">Speed</span><strong>{$vt_speed|default:'—'}</strong></div>
      {if $vt_avc}
      <div style="padding:8px 0 0;color:#98a2b8;font-size:12px">
        Keep your AVC ID handy if you ever transfer to another provider.</div>
      {/if}
    </div>
  </div>
  <div class="col-sm-6">
    <div style="background:#141b2b;border:1px solid #2a3347;border-radius:12px;padding:18px 20px;margin-bottom:18px;color:#e6e9f2">
      <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#98a2b8;margin-bottom:10px">Order progress</div>
      {if $vt_order_id}
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739">
          <span style="color:#98a2b8">Order</span><code style="background:#0a0e18;border-radius:6px;padding:1px 8px">{$vt_order_id}</code></div>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739">
          <span style="color:#98a2b8">Status</span><strong>{$vt_order_status_label|default:'—'}</strong></div>
      {else}
        <div style="color:#98a2b8;padding:6px 0">No orders in progress.</div>
      {/if}
      {if $vt_appointment_start}
        <div style="display:flex;justify-content:space-between;padding:6px 0">
          <span style="color:#98a2b8">Appointment</span>
          <span style="text-align:right"><strong>{$vt_appointment_start}</strong>{if $vt_appointment_end} – {$vt_appointment_end}{/if}
            <br><small style="color:#98a2b8">Status: {$vt_appointment_status}</small></span></div>
      {/if}
    </div>
  </div>
</div>

{* ---- on-premises equipment (from the last NBN health check) ---- *}
{if $vt_cpe && $vt_cpe.groups}
<div class="row">
  {foreach from=$vt_cpe.groups key=vtCpeGroup item=vtCpeItems}
  <div class="col-sm-6">
    <div style="background:#141b2b;border:1px solid #2a3347;border-radius:12px;padding:18px 20px;margin-bottom:18px;color:#e6e9f2">
      <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#98a2b8;margin-bottom:10px">
        {$vtCpeGroup|escape}</div>
      {foreach from=$vtCpeItems key=vtCpeLabel item=vtCpeValue}
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #1e2739;gap:10px">
          <span style="color:#98a2b8">{$vtCpeLabel|escape}</span>
          <code style="background:#0a0e18;border-radius:6px;padding:1px 8px;word-break:break-all">{$vtCpeValue|escape}</code>
        </div>
      {/foreach}
      {if $vtCpeGroup == 'Your router'}
        <div style="color:#667;font-size:11.5px;margin-top:8px">
          The device the NBN network sees plugged into your NBN port — if this MAC address doesn't
          match your router, the wrong device may be connected.</div>
      {/if}
    </div>
  </div>
  {/foreach}
</div>
<div style="color:#667;font-size:11.5px;margin:-8px 0 14px">
  Equipment details as seen by the NBN network on {$vt_cpe.at|date_format:'%e %b %Y'}.</div>
{/if}

{* ---- connection tools ---- *}
{if $vt_customer_tests}
<div style="background:#141b2b;border:1px solid #2a3347;border-radius:12px;padding:18px 20px;margin-bottom:18px;color:#e6e9f2">
  <div style="font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#98a2b8;margin-bottom:8px">Connection tools</div>
  <p style="margin:0 0 12px;color:#c7cede">Having trouble? Run a quick check on your line — results come
    straight from the NBN network.</p>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <select id="vtCliTestSel" class="form-control" style="max-width:340px;display:inline-block">
      {foreach from=$vt_customer_tests key=vtTestKey item=vtTestLabel}
        <option value="{$vtTestKey|escape}">{$vtTestLabel|escape}</option>
      {/foreach}
    </select>
    <button type="button" id="vtCliRun" class="btn btn-primary">Run check</button>
  </div>
  <small style="color:#98a2b8">Checks marked "brief dropout" restart your NBN equipment and
    interrupt the connection for a few minutes.</small>
</div>

{literal}
<script>
(function(){
  var btn=document.getElementById('vtCliRun');
  if(!btn||btn.dataset.kxBound){return;}
  btn.dataset.kxBound='1';
  var base='/modules/servers/virtutel_nbn/pages/service-test-api.php?serviceid={/literal}{$vt_serviceid}{literal}';
  function kxFetch(url){
    return fetch(url,{credentials:'same-origin'}).then(function(r){
      return r.text().then(function(t){
        try{return JSON.parse(t);}catch(e){}
        var m=t.match(/@@KXJSON@@([\s\S]*?)@@ENDKXJSON@@/);
        if(m){return JSON.parse(m[1]);}
        var snip=t.replace(/<script[\s\S]*?<\/script>/gi,' ')
          .replace(/<[^>]*>/g,' ').replace(/\s+/g,' ').trim().slice(0,200);
        throw new Error('HTTP '+r.status+' without payload. Response starts: "'+snip+'"');
      });
    });
  }
  btn.addEventListener('click',function(){
    var sel=document.getElementById('vtCliTestSel');
    var type=sel.value,label=sel.options[sel.selectedIndex].text;
    if(/dropout/i.test(label)&&!confirm('This restarts your NBN equipment and will drop your '
      +'connection for a few minutes. Continue?')){return;}
    var ov=document.createElement('div');
    ov.style.cssText='position:fixed;inset:0;background:rgba(10,14,24,.72);z-index:99999;'
      +'display:flex;align-items:center;justify-content:center';
    ov.innerHTML='<div style="background:#fff;color:#222;padding:26px 34px;border-radius:12px;'
      +'text-align:center;max-width:560px;width:92%;max-height:82vh;overflow:auto">'
      +'<div id="vtCliSpin" style="width:38px;height:38px;border:4px solid #dde3ee;'
      +'border-top-color:#1a5fd0;border-radius:50%;margin:0 auto 14px;animation:vtspin 1s linear infinite"></div>'
      +'<style>@keyframes vtspin{to{transform:rotate(360deg)}}</style>'
      +'<div id="vtCliMsg" style="font-weight:600">Starting '+label+'&hellip;</div>'
      +'<div id="vtCliSub" style="color:#667;font-size:12px;margin-top:6px">This usually takes '
      +'under a couple of minutes &mdash; hang tight.</div>'
      +'<button type="button" id="vtCliClose" class="btn btn-default btn-sm" '
      +'style="margin-top:14px">Close</button></div>';
    document.body.appendChild(ov);
    var closed=false;
    ov.querySelector('#vtCliClose').addEventListener('click',function(){closed=true;ov.remove();});
    function fail(msg){
      ov.querySelector('#vtCliSpin').style.display='none';
      ov.querySelector('#vtCliMsg').textContent=msg;
      ov.querySelector('#vtCliSub').textContent='';
    }
    kxFetch(base+'&do=run&testtype='+encodeURIComponent(type))
      .then(function(j){
        if(!j.ok){fail(j.error||'The check could not be started.');return;}
        ov.querySelector('#vtCliMsg').textContent='Check running…';
        var tries=0;
        (function poll(){
          if(closed){return;}
          if(++tries>60){fail('Still running — check back here in a few minutes.');return;}
          kxFetch(base+'&do=status&testid='+encodeURIComponent(j.id))
            .then(function(s){
              if(closed){return;}
              if(s.done){
                ov.querySelector('#vtCliSpin').style.display='none';
                ov.querySelector('#vtCliMsg').innerHTML=s.html;
                ov.querySelector('#vtCliSub').textContent='';
              }else{setTimeout(poll,3000);}
            })
            .catch(function(){setTimeout(poll,4000);});
        })();
      })
      .catch(function(e){fail('The check could not be started: '+e.message);});
  });
})();
</script>
{/literal}
{/if}
{/if}
