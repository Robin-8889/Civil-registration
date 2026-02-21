<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->certificate_number }}</title>
    <style>
        @media print {
            .no-print {
                display: none;
            }
            @page {
                margin: 0.5cm;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f5f5f5;
            padding: 20px;
        }

        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border: 15px double #1e3a8a;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #1e3a8a;
            padding-bottom: 20px;
        }

        .coat-of-arms {
            width: 80px;
            height: 80px;
            margin: 0 auto 15px;
            background: #1e3a8a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }

        .header h1 {
            font-size: 28px;
            color: #1e3a8a;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .header h2 {
            font-size: 18px;
            color: #666;
            margin-bottom: 10px;
        }

        .certificate-type {
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            border-radius: 5px;
        }

        .certificate-type h3 {
            font-size: 24px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }

        .certificate-body {
            margin: 30px 0;
            line-height: 1.8;
            font-size: 16px;
        }

        .info-section {
            margin: 25px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #1e3a8a;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px dotted #ccc;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            color: #1e3a8a;
            width: 40%;
        }

        .value {
            width: 60%;
            text-align: right;
        }

        .certificate-number {
            text-align: center;
            margin: 30px 0;
            padding: 15px;
            background: #fef3c7;
            border: 2px dashed #d97706;
            border-radius: 5px;
        }

        .certificate-number strong {
            color: #d97706;
            font-size: 18px;
        }

        .signature-section {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }

        .signature-box {
            width: 45%;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 10px;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 2px solid #1e3a8a;
            padding-top: 15px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .status-issued {
            background: #10b981;
            color: white;
        }

        .status-renewed {
            background: #3b82f6;
            color: white;
        }

        .status-cancelled {
            background: #ef4444;
            color: white;
        }

        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #1e3a8a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .print-button:hover {
            background: #1e40af;
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Certificate
    </button>

    <div class="certificate-container">
        <!-- Header -->
        <div class="header">
            <div class="coat-of-arms">TZ</div>
            <h1>United Republic of Tanzania</h1>
            <h2>Civil Registration Authority</h2>
            <p style="font-style: italic; color: #666;">Official Certificate of Registration</p>
        </div>

        <!-- Certificate Type -->
        <div class="certificate-type">
            <h3>{{ strtoupper($certificate->record_type) }} CERTIFICATE</h3>
        </div>

        <!-- Certificate Number -->
        <div class="certificate-number">
            <strong>Certificate No:</strong> {{ $certificate->certificate_number }}
            <br>
            <span class="status-badge status-{{ $certificate->status }}">
                {{ strtoupper($certificate->status) }}
            </span>
        </div>

        <!-- Certificate Body -->
        <div class="certificate-body">
            <p style="text-align: center; font-size: 18px; margin-bottom: 30px;">
                This is to certify that the following {{ $certificate->record_type }} has been officially registered
                in accordance with the laws of the United Republic of Tanzania.
            </p>

            <!-- Record Details -->
            <div class="info-section">
                @if($certificate->record_type === 'birth' && $recordData)
                    <h4 style="color: #1e3a8a; margin-bottom: 15px; text-align: center;">BIRTH REGISTRATION DETAILS</h4>

                    <div class="info-row">
                        <span class="label">Child's Full Name:</span>
                        <span class="value">{{ $recordData->child_first_name }} {{ $recordData->child_middle_name ?? '' }} {{ $recordData->child_last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Date of Birth:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($recordData->date_of_birth)->format('d F Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Place of Birth:</span>
                        <span class="value">{{ $recordData->place_of_birth }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Gender:</span>
                        <span class="value">{{ ucfirst($recordData->gender) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Father's Name:</span>
                        <span class="value">{{ $recordData->father_first_name }} {{ $recordData->father_last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Mother's Name:</span>
                        <span class="value">{{ $recordData->mother_first_name }} {{ $recordData->mother_last_name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Registration Date:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($recordData->registration_date)->format('d F Y') }}</span>
                    </div>

                @elseif($certificate->record_type === 'marriage' && $recordData)
                    <h4 style="color: #1e3a8a; margin-bottom: 15px; text-align: center;">MARRIAGE REGISTRATION DETAILS</h4>

                    <div class="info-row">
                        <span class="label">Groom's Full Name:</span>
                        <span class="value">{{ $recordData->groom->child_first_name ?? '' }} {{ $recordData->groom->child_middle_name ?? '' }} {{ $recordData->groom->child_last_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Bride's Full Name:</span>
                        <span class="value">{{ $recordData->bride->child_first_name ?? '' }} {{ $recordData->bride->child_middle_name ?? '' }} {{ $recordData->bride->child_last_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Date of Marriage:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($recordData->date_of_marriage)->format('d F Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Place of Marriage:</span>
                        <span class="value">{{ $recordData->place_of_marriage }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Type of Marriage:</span>
                        <span class="value">{{ ucfirst($recordData->marriage_type) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Registration Date:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($recordData->registration_date)->format('d F Y') }}</span>
                    </div>

                @elseif($certificate->record_type === 'death' && $recordData)
                    <h4 style="color: #1e3a8a; margin-bottom: 15px; text-align: center;">DEATH REGISTRATION DETAILS</h4>

                    <div class="info-row">
                        <span class="label">Deceased's Full Name:</span>
                        <span class="value">{{ $recordData->deceased->child_first_name ?? '' }} {{ $recordData->deceased->child_middle_name ?? '' }} {{ $recordData->deceased->child_last_name ?? '' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Date of Death:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($recordData->date_of_death)->format('d F Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Place of Death:</span>
                        <span class="value">{{ $recordData->place_of_death }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Cause of Death:</span>
                        <span class="value">{{ $recordData->cause_of_death }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Registration Date:</span>
                        <span class="value">{{ \Carbon\Carbon::parse($recordData->registration_date)->format('d F Y') }}</span>
                    </div>
                @endif
            </div>

            <!-- Certificate Issuance Details -->
            <div class="info-section" style="margin-top: 30px;">
                <h4 style="color: #1e3a8a; margin-bottom: 15px; text-align: center;">CERTIFICATE ISSUANCE DETAILS</h4>

                <div class="info-row">
                    <span class="label">Issue Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($certificate->issue_date)->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Issued By:</span>
                    <span class="value">{{ $certificate->issued_by }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Number of Copies:</span>
                    <span class="value">{{ $certificate->copies_issued }}</span>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>{{ $certificate->issued_by }}</strong><br>
                    <span style="font-size: 14px; color: #666;">Authorized Officer</span>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <strong>{{ \Carbon\Carbon::parse($certificate->issue_date)->format('d F Y') }}</strong><br>
                    <span style="font-size: 14px; color: #666;">Date of Issue</span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>Civil Registration Authority - United Republic of Tanzania</strong></p>
            <p>This is an official government document. Any alteration or forgery is punishable by law.</p>
            <p style="margin-top: 10px;">
                <em>Printed on: {{ now()->format('d F Y H:i') }}</em>
            </p>
        </div>
    </div>

    <script>
        // Auto-focus for printing
        window.onload = function() {
            // Optional: auto-print when page loads
            // Uncomment the line below if you want auto-print
            // window.print();
        };
    </script>
</body>
</html>
