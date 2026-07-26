<?php

/**
 * Legal & compliance documents rendered through the site shell
 * (kx_site_render('legal', $slug)).
 *
 * DRAFTS for launch: the structure follows what an Australian RSP must
 * publish (ACL, Privacy Act 1988 / APPs, ACMA Complaints Handling and
 * Financial Hardship industry standards, TCP Code CIS requirements) but
 * the text has not been reviewed by a lawyer. Replace the placeholders in
 * kx_legal_org() (entity name, ABN, address) and have the set reviewed
 * before relying on it.
 */

/** @return array<string,string> single source of the company facts */
function kx_legal_org(): array
{
    return [
        'trading' => 'Korvix',
        // PLACEHOLDERS — swap for the real registered entity + ABN.
        'entity' => 'Korvix Networks Pty Ltd',
        'abn' => '00 000 000 000',
        'address' => 'PO Box 000, Sale VIC 3850',
        'phone' => '03 4130 5012',
        'email' => 'support@korvix.co',
        'privacy_email' => 'privacy@korvix.co',
        'hardship_email' => 'hardship@korvix.co',
        'site' => 'korvix.co',
    ];
}

/** Estimated typical evening speed (7pm–11pm) for a plan's max download.
 *  ESTIMATES ONLY — replace with measured figures once live traffic data
 *  exists (the ACCC expects advertised TES to reflect real performance). */
function kx_legal_tes(int $down): string
{
    $tes = match (true) {
        $down >= 1000 => 780,
        $down >= 750 => 620,
        $down >= 500 => 440,
        $down >= 250 => 235,
        $down >= 100 => 97,
        $down >= 50 => 48,
        $down >= 25 => 24,
        default => 11,
    };

    return $tes . ' Mbps';
}

/** All legal docs: slug => [title, short tagline, updated (Y-m-d), html] */
function kx_legal_docs(): array
{
    $o = kx_legal_org();
    $e = fn ($v) => htmlspecialchars((string) $v, ENT_QUOTES);
    $updated = '2026-07-26';

    $who = $e($o['entity']) . ' (ABN ' . $e($o['abn']) . '), trading as '
        . $e($o['trading']) . ' ("we", "us", "our")';

    // ---------------------------------------------------------- Terms
    $terms = <<<HTML
<h2>1. About these terms</h2>
<p>These Terms of Service are the agreement between {$who} and you for the
supply of our internet and related services. By ordering, using or paying for
a service you accept these terms. Nothing in them excludes rights you have
under the Australian Consumer Law that cannot be excluded.</p>

<h2>2. The service</h2>
<p>We supply broadband services delivered over the National Broadband Network
(nbn&reg;) using wholesale inputs from our carrier partners. The service is a
data (layer 2) service: you connect your own compatible router to the NBN
equipment at your premises and your connection authenticates automatically —
no username or password is required.</p>

<h2>3. Service qualification</h2>
<p>Availability, technology type, achievable speeds and installation
requirements depend on your address and are confirmed by a service
qualification when you order. Results are provided by NBN Co and can change
between qualification and connection; if your address turns out to be unable
to support the plan you ordered we will offer the closest available
alternative or a full refund of anything paid.</p>

<h2>4. Connection and installation</h2>
<ul>
<li>Some connections need a technician appointment. We will tell you if yours
does and let you choose a time. Someone aged 18+ must be at the premises for
the appointment window.</li>
<li>If you miss a booked appointment without rescheduling in time, NBN Co may
charge a missed-appointment fee which we pass on at cost.</li>
<li>NBN equipment installed at your premises (such as the connection box /
NTD) remains the property of NBN Co and must not be removed or tampered
with.</li>
</ul>

<h2>5. Your equipment</h2>
<p>You are responsible for supplying and securing a compatible router.
Equipment must comply with Australian regulations. We provide reasonable
setup guidance for common routers but do not support faults inside your
home network or devices we did not supply.</p>

<h2>6. Billing and payments</h2>
<ul>
<li>Plans are billed monthly in advance. Your first invoice may include a
pro-rata amount plus any setup charges disclosed when you ordered.</li>
<li>Invoices are payable by the due date shown. We accept the payment methods
listed at checkout.</li>
<li>If an invoice remains unpaid after the due date we may contact you,
apply a late payment fee (disclosed on the invoice), restrict, suspend and
eventually cancel the service. We will always warn you before suspending or
cancelling for non-payment, and we will not suspend a service while a
hardship arrangement is being assessed or is in place (see our
<a href="/financial-hardship/">Financial Hardship Policy</a>).</li>
</ul>

<h2>7. Speeds</h2>
<p>Advertised speeds are maximums for your plan tier. Actual throughput
varies with your NBN technology type, in-home wiring and Wi-Fi, time of day,
and the servers you connect to. Typical evening speeds for each plan are in
that plan's <a href="/critical-information/">Critical Information
Summary</a>. If your line cannot reach the speed tier you pay for, we will
tell you and offer a lower tier with a refund of the difference, or
cancellation without penalty.</p>

<h2>8. Fair and acceptable use</h2>
<p>Residential plans include unlimited data for ordinary personal use and
are subject to our <a href="/acceptable-use/">Acceptable Use Policy</a>. We
may manage traffic where necessary to keep the network fair and stable, and
we will do so in accordance with our published policies.</p>

<h2>9. Moving house and transfers</h2>
<p>You can move your service to a new address (a new qualification applies)
or transfer (churn) to or from another provider. Your service identifier
(AVC ID) is shown in your customer portal and on invoices so you can give it
to a gaining provider. Transferring away ends your plan with us at the
transfer date; you remain responsible for charges up to that date.</p>

<h2>10. Cancellation</h2>
<p>There are no lock-in contracts on our residential plans. You can cancel
any time from the customer portal or by contacting us; the service ends at
the close of the current billing period unless you ask for an earlier date.
Amounts already billed for the current period are not refunded pro-rata
except where a consumer guarantee applies.</p>

<h2>11. Suspension or cancellation by us</h2>
<p>We may suspend or cancel the service if: an invoice remains unpaid after
warnings; you materially breach these terms or the Acceptable Use Policy;
we are required to by law; or our wholesale supply for your address ceases.
Where practical we will give you notice and a chance to fix the issue
first.</p>

<h2>12. Faults and support</h2>
<p>Report faults through the customer portal, by ticket, or on
{$e($o['phone'])}. We will diagnose the fault, run line tests, and where the
fault is in the NBN network we will lodge it with NBN Co and manage it on
your behalf. Some fault types require an NBN technician visit; charges may
apply where the fault is found to be in your own equipment or wiring and we
will tell you before you accept a visit that could attract a charge.</p>

<h2>13. Liability</h2>
<p>Our services come with guarantees under the Australian Consumer Law that
cannot be excluded. Subject to those guarantees, and to the maximum extent
permitted by law, our liability for any claim connected with the service is
limited to resupplying the service or the cost of resupply, and we are not
liable for indirect or consequential loss. The service is not a medical or
security alarm-grade service and may be unavailable during outages or power
failures — keep a mobile phone available for emergencies (000).</p>

<h2>14. Privacy</h2>
<p>We handle your personal information in accordance with our
<a href="/privacy/">Privacy Policy</a>.</p>

<h2>15. Complaints</h2>
<p>If something goes wrong, tell us — our
<a href="/complaints/">Complaints Handling Policy</a> explains the process
and timeframes. If we cannot resolve your complaint you can contact the
Telecommunications Industry Ombudsman (TIO) on 1800 062 058 or at
tio.com.au.</p>

<h2>16. Changes to these terms</h2>
<p>We may update these terms. For changes that are detrimental to you we
will give at least 30 days' notice by email, and if you do not accept the
change you may cancel without penalty before it takes effect.</p>

<h2>17. General</h2>
<p>These terms are governed by the laws of Victoria, Australia. If part of
these terms is unenforceable the rest still applies. Contact:
{$e($o['email'])} &middot; {$e($o['phone'])} &middot; {$e($o['address'])}.</p>
HTML;

    // -------------------------------------------------------- Privacy
    $privacy = <<<HTML
<p>{$who} is committed to protecting your personal information in accordance
with the Privacy Act 1988 (Cth) and the Australian Privacy Principles
(APPs). This policy explains what we collect, why, and your rights.</p>

<h2>1. What we collect</h2>
<ul>
<li><strong>Identity and contact details</strong> — name, email, phone,
service and billing addresses, and date of birth where identity checks are
required.</li>
<li><strong>Payment details</strong> — handled by our payment processors;
we do not store full card numbers on our systems.</li>
<li><strong>Service and technical data</strong> — your NBN location ID,
service identifiers (such as your AVC ID), connection equipment details,
line test and fault history, and session/connection records generated when
your service authenticates on our network.</li>
<li><strong>Support history</strong> — tickets, call notes and emails.</li>
</ul>

<h2>2. Why we collect it</h2>
<p>To qualify your address, connect and operate your service, bill you,
provide support and fault management, meet our legal obligations, and (with
your consent or as permitted by law) tell you about relevant products. You
can opt out of marketing at any time.</p>

<h2>3. Who we share it with</h2>
<ul>
<li><strong>Wholesale and network partners</strong> — our carrier partners
and NBN Co, to the extent needed to connect and operate your service (for
example your address, location ID and appointment contact details).</li>
<li><strong>Payment processors and billing providers</strong>.</li>
<li><strong>Law enforcement and government agencies</strong> — where
required by law, including obligations under the Telecommunications
(Interception and Access) Act 1979. Like all Australian carriage service
providers we are required to retain certain telecommunications data for two
years.</li>
<li><strong>Debt collection and credit reporting bodies</strong> — only for
unpaid accounts, after the warnings described in our terms.</li>
</ul>
<p>We do not sell your personal information.</p>

<h2>4. Storage and security</h2>
<p>Customer data is stored on systems located in Australia. We protect it
with encryption in transit, access controls, and staff access on a
need-to-know basis. No system is perfectly secure; if a data breach occurs
that is likely to result in serious harm we will notify you and the OAIC as
required by the Notifiable Data Breaches scheme.</p>

<h2>5. Cookies and our website</h2>
<p>Our website and customer portal use cookies for sign-in sessions and
basic analytics. You can block cookies in your browser, though the portal
may not function without session cookies.</p>

<h2>6. Access and correction</h2>
<p>You can access and update most of your details in the customer portal.
For anything else, email {$e($o['privacy_email'])} and we will respond
within 30 days. We may need to verify your identity first.</p>

<h2>7. Complaints</h2>
<p>Privacy complaints go to {$e($o['privacy_email'])} — we will acknowledge
within 2 working days and aim to resolve within 15 working days, consistent
with our <a href="/complaints/">Complaints Handling Policy</a>. If you are
not satisfied you can contact the Office of the Australian Information
Commissioner (OAIC) at oaic.gov.au or 1300 363 992.</p>

<h2>8. Contact</h2>
<p>Privacy Officer, {$e($o['entity'])}, {$e($o['address'])} &middot;
{$e($o['privacy_email'])} &middot; {$e($o['phone'])}.</p>
HTML;

    // ------------------------------------------------ Acceptable use
    $aup = <<<HTML
<p>This policy applies to every service supplied by {$who}. It exists to
keep the network safe, lawful and fair for everyone. Breaching it may lead
to warnings, suspension or cancellation under our
<a href="/terms/">Terms of Service</a>.</p>

<h2>1. Unlawful use</h2>
<p>You must not use the service to break any law — including to infringe
copyright or other intellectual property, access systems or data without
authorisation, distribute prohibited or illegal material, or harass,
menace or defraud any person.</p>

<h2>2. Spam and messaging</h2>
<p>You must not send unsolicited commercial electronic messages in breach
of the Spam Act 2003 (Cth), operate open mail relays, or falsify message
headers or origin information.</p>

<h2>3. Network abuse and security</h2>
<ul>
<li>No denial-of-service attacks, port scanning of third-party networks,
malware distribution, or attempts to bypass authentication or billing.</li>
<li>Keep your own equipment secure: change default router passwords,
secure your Wi-Fi, and keep firmware updated. You are responsible for
traffic that originates from your connection, including from compromised
or unsecured devices.</li>
<li>We may quarantine or filter traffic from your service where it is
actively harming the network or third parties, and will contact you when
we do.</li>
</ul>

<h2>4. Fair use of unlimited plans</h2>
<p>Unlimited residential plans are for ordinary personal household use.
Continuous commercial-scale use that materially degrades the experience of
other customers (for example operating a public server farm or reselling
capacity) is not ordinary use — we will contact you about a more suitable
service before taking any other action.</p>

<h2>5. Resale</h2>
<p>Residential services must not be resold or shared beyond your premises
without our written agreement.</p>

<h2>6. Reporting</h2>
<p>To report abuse originating from our network, or if you believe your
own connection is compromised, contact {$e($o['email'])}.</p>
HTML;

    // ------------------------------------------------------ Complaints
    $complaints = <<<HTML
<p>We would rather fix a problem than lose a customer. This policy follows
the ACMA Telecommunications (Consumer Complaints Handling) Industry
Standard. Making a complaint is always free.</p>

<h2>1. How to make a complaint</h2>
<ul>
<li><strong>Phone:</strong> {$e($o['phone'])}</li>
<li><strong>Online:</strong> open a ticket from your customer portal or the
Contact page</li>
<li><strong>Email:</strong> {$e($o['email'])}</li>
<li><strong>Mail:</strong> Complaints, {$e($o['entity'])},
{$e($o['address'])}</li>
</ul>
<p>You can appoint an authorised representative (family member, financial
counsellor, advocate) to deal with us on your behalf.</p>

<h2>2. What happens next</h2>
<ul>
<li>We acknowledge your complaint <strong>immediately</strong> if made by
phone, or within <strong>2 working days</strong> otherwise, and give you a
reference number.</li>
<li>We aim to resolve complaints on first contact. If we cannot, we will
propose a resolution within <strong>15 working days</strong>.</li>
<li><strong>Urgent complaints</strong> — where you have applied for
financial hardship assistance, or disconnection of your service is imminent
— are resolved within <strong>2 working days</strong>.</li>
<li>If we need more time we will explain why and give you a new timeframe,
and you may take the complaint to the TIO rather than wait.</li>
<li>We will not cancel or suspend your service just because you have
complained, and we will not start credit management action on an amount
that is the subject of an unresolved complaint.</li>
</ul>

<h2>3. If you are not satisfied</h2>
<p>Ask for the complaint to be escalated to a manager. If we still cannot
agree on a resolution, the Telecommunications Industry Ombudsman offers a
free, independent dispute resolution service:</p>
<p><strong>Telecommunications Industry Ombudsman (TIO)</strong><br>
Phone 1800 062 058 &middot; tio.com.au &middot; PO Box 276, Collins Street
West VIC 8007</p>
HTML;

    // ------------------------------------------------------- Hardship
    $hardship = <<<HTML
<p>If paying your bill is difficult right now, talk to us — help is
available, it is free, and your service will not be disconnected while we
assess your situation. This policy follows the Telecommunications
(Financial Hardship) Industry Standard 2024.</p>

<h2>1. What financial hardship is</h2>
<p>Any situation where you want to keep your service but are unable to pay
on time — whether short-term (illness, job loss, natural disaster, family
emergency, domestic or family violence) or longer-term (reduced income,
carer responsibilities). You do not need to be behind on payments to ask
for help.</p>

<h2>2. Options we can offer</h2>
<ul>
<li>A payment plan tailored to what you can afford</li>
<li>Extra time to pay an invoice</li>
<li>Moving to a cheaper plan without penalty</li>
<li>Waiving late payment fees</li>
<li>Temporarily pausing the service and billing</li>
<li>Applying part-payments to keep your service connected</li>
</ul>

<h2>3. How to apply</h2>
<p>Email {$e($o['hardship_email'])}, call {$e($o['phone'])}, or open a
ticket and mention financial hardship. A financial counsellor or authorised
representative can contact us for you. We may ask for a brief description
of your circumstances and, only where genuinely necessary, simple
supporting evidence — we will never make the process harder than it needs
to be.</p>

<h2>4. What we promise</h2>
<ul>
<li>We assess applications within <strong>5 working days</strong> and
confirm any arrangement in writing.</li>
<li>While an application is being assessed, and while you keep to an agreed
arrangement, we will not suspend, disconnect, or refer your account to debt
collection over the amounts covered.</li>
<li>Hardship assistance is free and confidential.</li>
</ul>

<h2>5. Free external support</h2>
<p><strong>National Debt Helpline</strong> — 1800 007 007 (free financial
counselling) &middot; ndh.org.au<br>
<strong>Mob Strong Debt Help</strong> — 1800 808 488<br>
If you are affected by domestic or family violence: <strong>1800RESPECT</strong>
— 1800 737 732.</p>
HTML;

    // ------------------------------------------------------------ CIS
    $plans = function_exists('kx_site_plans') ? kx_site_plans() : [];
    $cisBlocks = '';
    foreach ($plans as [$name, $price, $down, $up]) {
        $down = (int) $down;
        $up = (int) $up;
        $anchor = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name) ?? 'plan');
        $cisBlocks .= '<div class="cis" id="' . $e($anchor) . '">'
            . '<h2>' . $e($name) . '</h2>'
            . '<table>'
            . '<tr><th>Service</th><td>Unlimited-data nbn&reg; broadband at your qualified address. '
            . 'No phone line is included; this is a broadband-only service.</td></tr>'
            . '<tr><th>Plan speed</th><td>' . $e($down) . ' Mbps download / ' . $e($up) . ' Mbps upload (maximum)</td></tr>'
            . '<tr><th>Typical evening speed</th><td>' . $e(kx_legal_tes($down)) . ' download (7pm&ndash;11pm, estimate &mdash; '
            . 'actual speeds depend on your NBN technology, in-home setup and network conditions)</td></tr>'
            . '<tr><th>Monthly charge</th><td>$' . $e($price) . '</td></tr>'
            . '<tr><th>Minimum term</th><td>None &mdash; month to month, cancel any time</td></tr>'
            . '<tr><th>Setup</th><td>$0 standard connection. Non-standard NBN installation or missed-appointment '
            . 'charges from NBN Co are passed on at cost and disclosed before you accept them.</td></tr>'
            . '<tr><th>Minimum / maximum cost</th><td>Minimum $' . $e($price) . ' (one month). Maximum in any month: '
            . 'your plan fee plus any one-off charges you have accepted.</td></tr>'
            . '<tr><th>Hardware</th><td>BYO compatible router (no username/password &mdash; your service connects '
            . 'automatically). We can advise on suitable models.</td></tr>'
            . '<tr><th>Cancelling</th><td>Cancel from the customer portal or by contacting us; the service ends at the '
            . 'close of the billing period. No cancellation fee.</td></tr>'
            . '</table></div>';
    }
    if ($cisBlocks === '') {
        $cisBlocks = '<p>Plan summaries will appear here once plans are published.</p>';
    }
    $cis = <<<HTML
<p>A Critical Information Summary for each of our residential nbn&reg;
plans. All plans are month to month with unlimited data and no setup fee
for standard connections. Typical evening speeds are estimates pending
measured network data.</p>
{$cisBlocks}
<h2>Other things to know</h2>
<ul>
<li>Availability and achievable speed depend on your address &mdash; we
confirm both before you pay anything.</li>
<li>The service may not work during power or network outages; keep a
charged mobile for emergency (000) calls.</li>
<li>Full terms: <a href="/terms/">Terms of Service</a> &middot;
<a href="/acceptable-use/">Acceptable Use</a> &middot;
<a href="/privacy/">Privacy</a>.</li>
<li>Complaints: see our <a href="/complaints/">Complaints Handling
Policy</a>; unresolved complaints can go to the TIO on 1800 062 058.</li>
<li>Contact: {$e($o['phone'])} &middot; {$e($o['email'])}</li>
</ul>
HTML;

    return [
        'terms' => ['Terms of Service', 'The agreement covering every Korvix service.', $updated, $terms],
        'privacy' => ['Privacy Policy', 'What we collect, why, and your rights.', $updated, $privacy],
        'acceptable-use' => ['Acceptable Use Policy', 'The rules that keep the network fair and lawful.', $updated, $aup],
        'complaints' => ['Complaints Handling Policy', 'How to complain, our timeframes, and the TIO.', $updated, $complaints],
        'financial-hardship' => ['Financial Hardship Policy', 'Struggling to pay? Help is free and confidential.', $updated, $hardship],
        'critical-information' => ['Critical Information Summaries', 'The key facts for every plan, in one place.', $updated, $cis],
    ];
}
