{if $vt_error}
  <div class="alert alert-warning">{$vt_error}</div>
{elseif !$vt_linked}
  <div class="alert alert-info">Your NBN service is being set up — details will appear here shortly.</div>
{else}
  {if $vt_needs_booking}
    <div class="alert alert-warning">
      <strong>{if $vt_is_reschedule}Your installation appointment needs to be rebooked.{else}Your NBN installation needs an appointment.{/if}</strong>
      Pick a time that suits you — it only takes a minute.
      <p style="margin:10px 0 0">
        <a href="{$vt_booking_url}" class="btn btn-primary">
          {if $vt_is_reschedule}Choose a new time{else}Book your appointment{/if}
        </a>
      </p>
    </div>
  {/if}

  <div class="row">
    <div class="col-sm-6">
      <div class="panel panel-default">
        <div class="panel-heading">Connection</div>
        <table class="table">
          <tr><td>Technology</td><td><strong>{$vt_technology|default:'—'}</strong></td></tr>
          <tr><td>Speed</td><td>{$vt_speed|default:'—'}</td></tr>
          {if $vt_avc}<tr><td>AVC ID</td><td><code>{$vt_avc}</code><br>
            <small class="text-muted">You'll need this if you ever transfer to another provider.</small></td></tr>{/if}
        </table>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="panel panel-default">
        <div class="panel-heading">Order progress</div>
        <table class="table">
          {if $vt_order_id}
            <tr><td>Order</td><td><code>{$vt_order_id}</code></td></tr>
            <tr><td>Status</td><td>{$vt_order_status|default:'—'}</td></tr>
          {else}
            <tr><td colspan="2">No orders in progress.</td></tr>
          {/if}
          {if $vt_appointment_start}
            <tr><td>Appointment</td>
              <td>{$vt_appointment_start}{if $vt_appointment_end} – {$vt_appointment_end}{/if}
                <br><small class="text-muted">Status: {$vt_appointment_status}</small></td></tr>
          {/if}
        </table>
      </div>
    </div>
  </div>

  {if $vt_customer_tests}
  <div class="panel panel-default">
    <div class="panel-heading">Connection tools</div>
    <div class="panel-body">
      <p style="margin:0 0 10px">Having trouble? Run a quick check on your line — results come
        straight from the NBN network.</p>
      <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <select id="vtCliTestSel" class="form-control" style="max-width:340px;display:inline-block">
          {foreach from=$vt_customer_tests key=vtTestKey item=vtTestLabel}
            <option value="{$vtTestKey|escape}">{$vtTestLabel|escape}</option>
          {/foreach}
        </select>
        <button type="button" id="vtCliRun" class="btn btn-primary">Run check</button>
      </div>
      <small class="text-muted">Checks marked "brief dropout" restart your NBN equipment and
        interrupt the connection for a few minutes.</small>
    </div>
  </div>

  {literal}
  <script>
  (function(){
    var btn=document.getElementById('vtCliRun');
    if(!btn||btn.dataset.kxBound){return;}
    btn.dataset.kxBound='1';
    var base='clientarea.php?action=productdetails&id={/literal}{$vt_serviceid}{literal}&modop=custom';
    function kxFetch(url){
      return fetch(url,{credentials:'same-origin'}).then(function(r){
        return r.text().then(function(t){
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
      kxFetch(base+'&a=runtest&testtype='+encodeURIComponent(type))
        .then(function(j){
          if(!j.ok){fail(j.error||'The check could not be started.');return;}
          ov.querySelector('#vtCliMsg').textContent='Check running…';
          var tries=0;
          (function poll(){
            if(closed){return;}
            if(++tries>60){fail('Still running — check back here in a few minutes.');return;}
            kxFetch(base+'&a=teststatus&testid='+encodeURIComponent(j.id))
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
