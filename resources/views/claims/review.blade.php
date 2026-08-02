<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PE Portal - Claim Review</title>
<link rel="icon" href="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png" type="image/png">
<style>
    body { margin: 0; padding: 0; background-color: #eef1f5; font-family: Arial, sans-serif; }
    .wrapper { display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 10px; }
    .container { width: 100%; max-width: 600px; background: #ffffff; border-radius: 8px; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); overflow: hidden; }
    .header { text-align: center; padding: 32px 20px 20px; }
    .header img { width: 50px; margin-bottom: 12px; }
    .header-title { font-size: 22px; font-weight: 700; color: #111827; }
    .divider { height: 1px; background-color: #f3f4f6; margin: 0 40px; }
    .content { padding: 20px 40px 40px 40px; color: #374151; }
    .content h2 { text-align: center; font-size: 20px; margin-bottom: 20px; color: #111827; }
    .detail-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 18px; margin-bottom: 20px; background: #f9fafb; }
    .detail-box div { margin-bottom: 8px; font-size: 14px; }
    .item-box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 14px; margin-bottom: 12px; background: #ffffff; }
    .item-title { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 6px; }
    .item-meta { font-size: 13px; color: #4b5563; margin-bottom: 6px; }
    .status { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fee2e2; color: #991b1b; }
    .item-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 12px; }
    .actions { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .button { display: block; width: 100%; padding: 14px; color: white; text-align: center; font-weight: 600; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; }
    .button-small { padding: 10px; font-size: 13px; }
    .button-review { background-color: #111827; }
    .accept { background-color: #16a34a; }
    .reject { background-color: #dc2626; }
    .footer { text-align: center; padding: 32px 40px; background-color: #f9fafb; border-top: 1px solid #e5e7eb; font-size: 12px; }
    @media only screen and (max-width: 620px) { .container { border-radius: 0; } .content { padding: 10px 20px 20px 20px; } .footer { padding: 20px; } }
</style>
</head>

<body>
<div class="wrapper">
    <div class="container">
        <div class="header">
            <img src="https://www.petro-excel.com.my/wp-content/uploads/2018/09/Oil-Drop-Out-line-e1736841035299.png">
            <div class="header-title">Petro-Excel Sdn Bhd</div>
        </div>

        <div class="divider"></div>

        <div class="content">
            <h2>{{ $title }}</h2>

            <p>Hello <strong>{{ $name }}</strong>,</p>
            <p>Please approve or reject each claim item first. Once all items are done, you can review the claim.</p>

            <div class="detail-box">
                <div><strong>Applicant:</strong> {{ $claim_header->user->email }}</div>
                <div><strong>Claim:</strong> {{ $claim_header->name }}</div>
                <div><strong>Total Amount:</strong> {{ number_format($claim_header->total_amount, 2) }}</div>
                <div><strong>Remark:</strong> {{ $claim_header->remark ?: '-' }}</div>
            </div>

            @foreach($claim_items as $item)
                @php
                    $action_at = $type == 'manager' ? $item->manager_action_at : $item->director_action_at;
                    $approved = $type == 'manager' ? $item->manager_approved : $item->director_approved;
                @endphp

                <div class="item-box">
                    <div class="item-title">{{ $loop->iteration }}. {{ $item->name }}</div>
                    <div class="item-meta"><strong>Amount:</strong> {{ number_format($item->amount, 2) }}</div>
                    <div class="item-meta"><strong>Date:</strong> {{ $item->date ? \Carbon\Carbon::parse($item->date)->format('Y-m-d') : '-' }}</div>
                    <div class="item-meta"><strong>Remark:</strong> {{ $item->remark ?: '-' }}</div>
                    <div class="item-meta">
                        <strong>Status:</strong>
                        @if($action_at)
                            @if($approved)
                                <span class="status status-approved">Approved</span>
                            @else
                                <span class="status status-rejected">Rejected</span>
                            @endif
                        @else
                            <span class="status status-pending">Pending</span>
                        @endif
                    </div>

                    @if(!$action_at)
                        <form method="POST" action="{{ $approve_item_url }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email }}">
                            <input type="hidden" name="claim_header_uuid" value="{{ $claim_header_uuid }}">
                            <input type="hidden" name="claim_item_uuid" value="{{ $item->uuid }}">
                            <input type="hidden" name="type" value="{{ $type }}">

                            <div class="item-actions">
                                <button class="button button-small accept" name="approve" value="1">Approve Item</button>
                                <button class="button button-small reject" name="approve" value="0">Reject Item</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endforeach

            @if($pending_item_count > 0)
                <div class="detail-box">
                    <div><strong>Pending Items:</strong> {{ $pending_item_count }}</div>
                    <div>Please approve or reject all claim items before reviewing the claim.</div>
                </div>
            @else
                <form method="POST" action="{{ $action_url }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="claim_header_uuid" value="{{ $claim_header_uuid }}">
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="approve" value="1">

                    <div class="actions" style="grid-template-columns: 1fr;">
                        <button class="button button-review">Review Claim</button>
                    </div>
                </form>
            @endif
        </div>

        <div class="footer">
            <p style="margin: 0 0 24px 0; font-size: 12px; color: #9ca3af;">This is an automated system-generated email. Please do not reply to this message.</p>
            <p style="margin: 0 0 8px 0; font-size: 12px; color: #6b7280; font-weight: 600;">Petro-Excel Sdn Bhd</p>
            <p style="margin: 0; font-size: 12px; color: #9ca3af; line-height: 18px;">Lot 1236 & 1237,<br>Senadin Venture Light Industrial Park,<br>Jalan Lutong - Kuala Baram,<br>98000 Miri, Sarawak.<br></p>
            <p style="margin: 16px 0 0 0; font-size: 12px; color: #9ca3af;">© {{ date('Y') }} Petro-Excel Sdn Bhd. All rights reserved.</p>
        </div>
    </div>
</div>
</body>
</html>
