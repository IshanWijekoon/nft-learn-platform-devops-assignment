<?php
include 'db.php';
include 'nft_certificate_system.php';

$verification_code = $_GET['code'] ?? '';
$certificate_data = null;
$error_message = '';

if ($verification_code) {
    $verification_result = verifyCertificate($verification_code);
    if ($verification_result['success']) {
        $certificate_data = $verification_result['certificate'];
    } else {
        $error_message = $verification_result['message'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NFT Certificate Verification - Learnity</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #121212 0%, #1a1a1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            color: #e0e0e0;
        }

        .verification-container {
            background: #1e1e1e;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .content {
            padding: 2rem;
        }

        .search-form {
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #ffffff;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #444444;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
            background: #2a2a2a;
            color: #e0e0e0;
        }

        .form-group input:focus {
            outline: none;
            border-color: #4a90e2;
        }

        .form-group input::placeholder {
            color: #888888;
        }

        .btn {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .certificate-display {
            background: #2a2a2a;
            border-radius: 10px;
            padding: 2rem;
            margin-top: 2rem;
        }

        .certificate-image {
            text-align: center;
            margin-bottom: 2rem;
        }

        .certificate-image img {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .certificate-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .detail-item {
            background: #3a3a3a;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }

        .detail-label {
            font-weight: 600;
            color: #4a90e2;
            margin-bottom: 0.5rem;
        }

        .detail-value {
            color: #ffffff;
            word-break: break-all;
        }

        .verification-status {
            background: #1b5e20;
            color: #a5d6a7;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #4caf50;
            margin-bottom: 2rem;
        }

        .error-message {
            background: #b71c1c;
            color: #ffcdd2;
            padding: 1rem;
            border-radius: 8px;
            border-left: 4px solid #f44336;
            margin-bottom: 2rem;
        }

        .blockchain-info {
            background: linear-gradient(135deg, #4a90e2 0%, #357abd 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }

        .blockchain-info h3 {
            margin-bottom: 1rem;
        }

        .footer {
            text-align: center;
            padding: 2rem;
            background: #2a2a2a;
            color: #b0b0b0;
        }
    </style>
</head>
<body>
    <div class="verification-container">
        <div class="header">
            <h1><i class="fas fa-certificate"></i> Learnity Certificate Verification</h1>
            <p>Verify the authenticity of Learnity learning certificates</p>
        </div>

        <div class="content">
            <?php if (!$verification_code): ?>
                <form class="search-form" method="GET">
                    <div class="form-group">
                        <label for="code">Enter Verification Code:</label>
                        <input type="text" id="code" name="code" placeholder="Enter 8-character verification code" maxlength="8" required>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-search"></i> Verify Certificate
                    </button>
                </form>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Verification Failed:</strong> <?php echo htmlspecialchars($error_message); ?>
                </div>
                <a href="verify_certificate.php" class="btn">Try Another Code</a>
            <?php endif; ?>

            <?php if ($certificate_data): ?>
                <div class="verification-status">
                    <i class="fas fa-check-circle"></i>
                    <strong>Certificate Verified!</strong> This is a valid NFT certificate issued by Learnity.
                </div>

                <div class="certificate-display">
                    <?php if ($certificate_data['certificate_image_path']): ?>
                        <div class="certificate-image">
                            <img src="<?php echo htmlspecialchars($certificate_data['certificate_image_path']); ?>" 
                                 alt="NFT Certificate">
                        </div>
                    <?php endif; ?>

                    <div class="certificate-details">
                        <div class="detail-item">
                            <div class="detail-label">Certificate Holder</div>
                            <div class="detail-value"><?php echo htmlspecialchars($certificate_data['learner_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Course Name</div>
                            <div class="detail-value"><?php echo htmlspecialchars($certificate_data['course_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Issued By</div>
                            <div class="detail-value"><?php echo htmlspecialchars($certificate_data['creator_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Issue Date</div>
                            <div class="detail-value"><?php echo date('F j, Y', strtotime($certificate_data['issued_at'])); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">NFT Key</div>
                            <div class="detail-value"><?php echo htmlspecialchars($certificate_data['nft_key']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label">Certificate Hash</div>
                            <div class="detail-value"><?php echo htmlspecialchars($certificate_data['certificate_hash']); ?></div>
                        </div>
                    </div>

                    <div class="blockchain-info">
                        <h3><i class="fas fa-link"></i> Blockchain Information</h3>
                        <p>This certificate is secured using blockchain technology. The unique hash and NFT key above serve as proof of authenticity and cannot be duplicated or forged.</p>
                        <p><strong>Status:</strong> <?php echo ucfirst($certificate_data['status']); ?></p>
                        <?php if ($certificate_data['blockchain_tx_hash']): ?>
                            <p><strong>Transaction Hash:</strong> <?php echo htmlspecialchars($certificate_data['blockchain_tx_hash']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                
            <?php endif; ?>
        </div>

        <div class="footer">
            <p>&copy; 2025 Learnity - NFT Learning Platform. All rights reserved.</p>
            <p>This verification system is powered by blockchain technology for maximum security.</p>
        </div>
    </div>
</body>
</html>
