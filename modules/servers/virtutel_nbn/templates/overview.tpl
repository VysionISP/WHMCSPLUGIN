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
{/if}
