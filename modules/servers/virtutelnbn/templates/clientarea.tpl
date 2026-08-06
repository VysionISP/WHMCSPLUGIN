<div class="row">
    <div class="col-md-12">
        <h3>NBN Connection</h3>
        <table class="table table-striped">
            <tr>
                <td><strong>Connection Status</strong></td>
                <td>
                    {if $nbnStatus == 'active'}
                        <span class="label label-success">Active</span>
                    {elseif $nbnStatus == 'in_progress' || $nbnStatus == 'pending'}
                        <span class="label label-info">Provisioning</span>
                    {elseif $nbnStatus == 'held'}
                        <span class="label label-warning">On Hold</span>
                    {elseif $nbnStatus == 'suspended'}
                        <span class="label label-warning">Suspended</span>
                    {else}
                        <span class="label label-default">{$nbnStatus|capitalize}</span>
                    {/if}
                </td>
            </tr>
            {if $avcId}
            <tr>
                <td><strong>AVC ID</strong></td>
                <td>{$avcId}</td>
            </tr>
            {/if}
            {if $planCode}
            <tr>
                <td><strong>Plan</strong></td>
                <td>{$planCode}</td>
            </tr>
            {/if}
        </table>
    </div>
</div>
