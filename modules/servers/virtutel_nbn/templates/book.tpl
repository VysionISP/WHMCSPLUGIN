<h3>NBN Installation Appointment</h3>

{if $vt_error}
  <div class="alert alert-danger">{$vt_error}</div>
  <a class="btn btn-default" href="clientarea.php?action=productdetails&id={$vt_serviceid}&modop=custom&a=bookappointment">Try again</a>

{elseif $vt_confirmed}
  <div class="alert alert-success">
    <strong>Appointment reserved:</strong> {$vt_slot_label}
    <br>Reference: <code>{$vt_appointment_id}</code>
  </div>
  <p>You'll receive confirmation once NBN locks it in. Someone over 18 needs to be at the property
     for the appointment window. We'll let you know if anything changes.</p>
  <a class="btn btn-default" href="clientarea.php?action=productdetails&id={$vt_serviceid}">Back to service</a>

{elseif $vt_not_needed}
  <div class="alert alert-info">
    No appointment booking is needed for this service right now.
    {if $vt_slot_label}<br><strong>Current appointment:</strong> {$vt_slot_label} ({$vt_appointment_status}){/if}
  </div>
  <a class="btn btn-default" href="clientarea.php?action=productdetails&id={$vt_serviceid}">Back to service</a>

{else}
  <p>
    {if $vt_mode == 'reschedule'}
      Your original appointment couldn't go ahead — pick a new time below.
    {else}
      An NBN technician needs to visit to complete your connection. Pick the window that suits you —
      someone over 18 must be at the property for the full window.
    {/if}
  </p>

  <form method="post"
        action="clientarea.php?action=productdetails&id={$vt_serviceid}&modop=custom&a=bookappointment">
    <div class="list-group" style="max-width:520px">
      {foreach $vt_slots as $slot}
        <label class="list-group-item" style="display:block;cursor:pointer">
          <input type="radio" name="vt_slot" value="{$slot.value}" required style="margin-right:10px">
          {$slot.label}
        </label>
      {/foreach}
    </div>
    <button type="submit" class="btn btn-primary">
      {if $vt_mode == 'reschedule'}Reschedule appointment{else}Reserve this time{/if}
    </button>
  </form>
{/if}
