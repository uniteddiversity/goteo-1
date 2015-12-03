<?php

use Goteo\Library\Paypal;

$invest = $this->invest;
$project = $this->project;
$calls = $this->calls;
$droped = $this->droped;
$user = $this->user;
$methods = $this->methods;
$rewards = $invest->rewards;
array_walk($rewards, function (&$reward) { $reward = $reward->reward; });

?>

<?php $this->layout('admin/layout') ?>

<?php $this->section('admin-content') ?>

<a href="/admin/accounts/" class="button"><?= $this->text('admin-back') ?></a>

<div class="widget">
    <h3><?= $this->text('admin-account-detail') ?></h3>
    <table>
    <tr>
        <td><strong><?= $this->text('admin-project') ?></strong></td>
        <td><?php echo $project->name ?> (<?php echo $this->projectStatus[$project->status] ?>)</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><strong><?= $this->text('admin-user') ?></strong></td>
        <td>
            <?php if($this->is_module_admin('Users', $project->node)): ?>
                <a href="/admin/users/manage/<?= $user->id ?>"><?= $user->id ?> [<?= $user->name ?> / <?= $user->email ?>]</a>
            <?php else: ?>
            <?= $user->id ?> [<?= $user->name ?> / <?= $user->email ?>]

            <?php endif ?>
        </td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td><?= $this->text('admin-account-amount') ?>:</td>
        <td><?php echo $invest->amount ?> &euro;
            <?php
                if (!empty($invest->campaign))
                    echo 'Campaña: ' . $campaign->name;
            ?>
        </td>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td><?= $this->text('admin-status') ?>:</td>
        <td><?= $this->percent_span(100 * ($invest->status + 1)/2, 0, $this->investStatus[$invest->status]) ?>
            <?php if ($invest->status < 0) echo ' <span style="font-weight:bold; color:red;">OJO! que este aporte no fue confirmado.<span>';
                  if ($invest->issue) echo ' <span style="font-weight:bold; color:red;">INCIDENCIA!<span>';
            ?>

        </td>
        <td>

            <?php if ($invest->method == 'paypal' && $invest->status == 0) : ?>
            <a href="/admin/accounts/execute/<?php echo $invest->id ?>"
                onclick="return confirm('¿Seguro que quieres ejecutar ahora el cargo del preapproval?');"
                class="button">Ejecutar cargo ahora</a><br>
            <?php endif; ?>

            <?php if ($invest->issue) : ?>
            <a href="/admin/accounts/solve/<?php echo $invest->id ?>" onclick="return confirm('Esta incidencia se dará por resuelta: se va a cancelar el preaproval, el aporte pasará a ser de tipo Cash y en estado Cobrado por goteo, seguimos?')" class="button"><?= $this->text('admin-account-issue-solved') ?></a><br>
            <?php endif; ?>

            <?php if ($this->poolable) : ?>
            <a href="/admin/accounts/refundpool/<?php echo $invest->id ?>" onclick="return confirm('<?= $this->ee($this->text('admin-account-refund-to-pool-confirm'), 'js') ?>')" class="button"><?= $this->text('admin-account-refund-to-pool') ?></a>
            <?php endif; ?>

            <?php if ( $this->refundable) : ?>
            <a href="/admin/accounts/refunduser/<?php echo $invest->id ?>" onclick="return confirm('<?= $this->ee($this->text('admin-account-refund-to-user-confirm'), 'js') ?>')" class="button"><?= $this->text('admin-account-refund-to-user') ?></a><br>
            <?php endif; ?>

            <a href="/admin/accounts/update/<?php echo $invest->id ?>" onclick="return confirm('<?= $this->ee($this->text('admin-account-modify-status-confirm'), 'js') ?>')" class="button"><?= $this->text('admin-account-modify-status') ?></a>
        </td>
    </tr>

    <tr>
        <td><?= $this->text('admin-account-invest-date') ?>:</td>
        <td><?php echo $invest->invested . '  '; ?>
            <?php
                if (!empty($invest->charged))
                    echo "<br>\nCargo ejecutado el: " . $invest->charged;

                if (!empty($invest->returned))
                    echo "<br>\nDinero devuelto el: " . $invest->returned;
            ?>
        </td>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td><?= $this->text('admin-account-donation') ?>:</td>
        <td>
            <?php
            if($invest->resign) {
                echo "SI<br />Donativo de: {$invest->address->name} [{$invest->address->nif}]";
            }
            else {
                echo "NO";
            }
            ?>
        </td>
        <td><a href="/admin/accounts/switchresign/<?php echo $invest->id ?>" onclick="return confirm('<?= $this->ee($this->text('admin-account-switch-donation-confirm'), 'js') ?>')" class="button"><?= $this->text('admin-account-switch-donation') ?></a></td>
    </tr>

    <tr>
        <td><?= $this->text('admin-account-pool-on-fail') ?>:</td>
        <td>
            <?= ($invest->pool) ?  $this->text('admin-YES') : $this->text('admin-NO')  ?>
        </td>
        <td>
                <a href="/admin/accounts/switchpool/<?php echo $invest->id ?>" onclick="return confirm('<?= $this->ee($this->text('admin-account-switch-pool-confirm'), 'js') ?>')" class="button"><?= $this->text('admin-account-switch-pool') ?></a>
        </td>
    </tr>

    <tr>
        <td><?= $this->text('admin-account-payment-method') ?>:</td>
        <td><?php echo $methods[$invest->method] . '   '; ?>
            <?php
                if (!empty($invest->campaign))
                    echo '<br />Capital riego';

                if (!empty($invest->anonymous))
                    echo '<br />Aporte anónimo';

                if (!empty($invest->admin))
                    echo '<br />Manual generado por admin: '.$invest->admin;
            ?>
        </td>
        <td>&nbsp;</td>
    </tr>

    <tr>
        <td><?= $this->text('admin-account-transaction') ?>:</td>
        <td><?php
                if (!empty($invest->preapproval)) {
                    echo 'Preapproval: '.$invest->preapproval . '   ';
                }

                if (!empty($invest->payment)) {
                    echo "<br>\nCargo: ".$invest->payment . '   ';
                }
            ?>
        </td>
        <td>&nbsp;</td>
    </tr>

    <?php if (!empty($invest->rewards)) : ?>
    <tr>
        <td><?= $this->text('admin-account-rewards') ?>:</td>
        <td>
            <?php echo implode(', ', $rewards); ?>
        </td>
        <td><a href="/admin/rewards/edit/<?php echo $invest->id ?>" class="button"><?= $this->text('admin-account-edit-reward') ?></a></td>
    </tr>
    <?php endif; ?>

    <tr>
        <td><?= $this->text('admin-address') ?>:</td>
        <td>
            <?php echo $invest->address->address; ?>,
            <?php echo $invest->address->location; ?>,
            <?php echo $invest->address->zipcode; ?>
            <?php echo $invest->address->country; ?>
        </td>
        <td>&nbsp;</td>
    </tr>

    </table>

    <?php if ($invest->method == 'paypal') : ?>
        <?php if ($this->get_query('full') != 'show') : ?>
        <p>
            <a href="/admin/accounts/details/<?php echo $invest->id; ?>?full=show">Mostrar detalles técnicos</a>
        </p>
        <?php endif; ?>

        <?php if (!empty($invest->transaction)) : ?>
        <dl>
            <dt><strong>Detalles de la devolución:</strong></dt>
            <dd>Hay que ir al panel de paypal para ver los detalles de una devolución</dd>
        </dl>
        <?php endif ?>
    <?php elseif ($invest->method == 'tpv') : ?>
        <p>Hay que ir al panel del banco para ver los detalles de los aportes mediante TPV.</p>
    <?php endif ?>

    <?php if (!empty($droped)) : ?>
    <h3>Capital riego asociado</h3>
    <dl>
        <dt>Convocatoria:</dt>
        <dd><?php echo $calls[$droped->call] ?></dd>
    </dl>
    <a href="/admin/invests/details/<?php echo $droped->id ?>" target="_blank">Ver aporte completo de riego</a>
    <?php endif; ?>

</div>

<div class="widget">
    <h3>Log</h3>
    <?php foreach ($invest->getDetails() as $log)  {
        echo "{$log->date} : {$log->log} ({$log->type})<br />";
    } ?>
</div>

<?php if ($this->get_query('full') == 'show') :

    $errors = array();
    ?>
<div class="widget">
    <h3>Detalles técnicos de la transaccion</h3>
    <?php
    // detalles de preapproval
    if (!empty($invest->preapproval)) :
        $details = Paypal::preapprovalDetails($invest->preapproval, $errors);
        ?>
    <dl>
        <dt><strong>Detalles del preapproval:</strong></dt>
        <dd><?php echo \trace($details); ?></dd>
    </dl>
    <?php endif ?>

    <?php
    // detalles de la ejecución del preapproval
    if (!empty($invest->preapproval) && !empty($invest->payment)) :
        $details = Paypal::paymentDetails($invest->payment, $errors);
        ?>
    <dl>
        <dt><strong>Detalles del cargo:</strong></dt>
        <dd><?php echo \trace($details); ?></dd>
    </dl>
    <?php endif; ?>

    <?php
    // detalles de una transacción PayPal con ExpressCheckout
    if (empty($invest->preapproval) && !empty($invest->transaction)) :
        $details = Paypal::payDetails($invest->transaction, $errors);
        ?>
    <dl>
        <dt><strong>Detalles del pago:</strong></dt>
        <dd><?php echo \trace($details); ?></dd>
    </dl>
    <?php endif; ?>

    <?php
    if (!empty($errors)) {
        echo '<div>'.implode('<br />', $errors).'</div>';
    }
    ?>
</div>
<?php endif; ?>

<?php $this->replace() ?>