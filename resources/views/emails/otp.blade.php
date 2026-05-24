<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MONOFRAME SECURITY CODE</title>
</head>
<body style="margin:0;padding:0;background-color:#0f172a;font-family:'Segoe UI',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;padding:40px 20px;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:linear-gradient(145deg,#1e293b,#0f172a);border-radius:24px;border:1px solid #334155;overflow:hidden;">
                    <!-- Header -->
                    <tr>
                        <td style="padding:40px 32px 20px;text-align:center;">
                            <div style="width:56px;height:56px;border-radius:16px;background:linear-gradient(135deg,#3b82f6,#6366f1);display:inline-block;text-align:center;line-height:56px;margin-bottom:16px;">
                                <span style="font-size:24px;">&#128274;</span>
                            </div>
                            <h2 style="margin:0;color:#f1f5f9;font-size:20px;font-weight:800;letter-spacing:1px;">MONOFRAME SECURITY CODE</h2>
                            <p style="margin:8px 0 0;color:#94a3b8;font-size:13px;">Kode verifikasi untuk akun Anda</p>
                        </td>
                    </tr>

                    <!-- OTP Code -->
                    <tr>
                        <td style="padding:20px 32px;text-align:center;">
                            <div style="background:#1e293b;border:1px solid #334155;border-radius:16px;padding:24px;">
                                <p style="margin:0 0 12px;color:#64748b;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:2px;">Kode Verifikasi Anda</p>
                                <p style="margin:0;font-size:42px;font-weight:900;letter-spacing:8px;color:#3b82f6;font-family:'Courier New',monospace;">{{ $otpCode }}</p>
                            </div>
                        </td>
                    </tr>

                    <!-- Info -->
                    <tr>
                        <td style="padding:8px 32px 24px;text-align:center;">
                            <p style="margin:0;color:#64748b;font-size:12px;">
                                <span style="color:#f59e0b;">&#9200;</span> Kode berlaku hingga <strong style="color:#f1f5f9;">{{ $expiresAt }}</strong><br>
                                Kode ini hanya berlaku <strong style="color:#ef4444;">sekali pakai</strong>.
                            </p>
                        </td>
                    </tr>

                    <!-- Divider -->
                    <tr>
                        <td style="padding:0 32px;">
                            <div style="height:1px;background:linear-gradient(90deg,transparent,#334155,transparent);"></div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 32px 32px;text-align:center;">
                            <p style="margin:0;color:#475569;font-size:11px;">
                                Abaikan email ini jika Anda tidak meminta reset password.<br>
                                <strong style="color:#64748b;">MONOFRAME STUDIO</strong> &mdash; Secure Enterprise POS
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
