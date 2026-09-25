<?php
if(empty($_SESSION['role']) || $_SESSION['role'] !== 'admin')
    {
        echo"<script>alert('Access denied.')</script>";
        echo"<script>window.location.href='../Artisans/login.php';</script>";
    }
$artisan_id = (int) $_GET['review_artisan'];

$stmt = mysqli_prepare($conn, "select * from artisans where artisan_id=?");
mysqli_stmt_bind_param($stmt, "i", $artisan_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$artisan = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$artisan) {
    echo "<script>alert('Artisan not found'); window.location.href='./index.php?view_artisans';</script>";
    exit;
}
?>
<style>
.products-card{
    max-width: 700px;
    margin: 20px auto;
    padding: 40px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    border: 1px solid #eef1f4;
}
.products-card h2{
    color: #1c2b3a;
    font-size: 1.5rem;
    font-weight: 700;
    border-bottom: 1px solid #eef1f4;
    padding-bottom: 16px;
    margin: 0 0 20px 0;
}
.products-card h3{
    color: #1c2b3a;
    font-size: 1.1rem;
    margin-top: 24px;
    margin-bottom: 10px;
}
.detail-row{
    display: flex;
    padding: 8px 0;
    border-bottom: 1px solid #f3f5f7;
    font-size: 14px;
}
.detail-row strong{
    width: 160px;
    flex-shrink: 0;
    color: #4a5568;
}
.doc-list{
    list-style: none;
    padding: 0;
    margin: 0;
}
.doc-list li{
    padding: 8px 0;
    border-bottom: 1px solid #f3f5f7;
    font-size: 14px;
}
.doc-list a{
    color: #0cc012;
    font-weight: 600;
    text-decoration: none;
}
.doc-list a:hover{
    text-decoration: underline;
}
.review-actions{
    margin-top: 24px;
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}
.btn-approve, .btn-revoke, .btn-back{
    display: inline-block;
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    text-align: center;
    text-decoration: none;
    border: 1px solid transparent;
    border-radius: 8px;
    cursor: pointer;
}
.btn-approve{
    background-color: #e9f9eb;
    border-color: #cdebd0;
    color: #1a8a24;
}
.btn-approve:hover{ background-color: #d9f2db; }
.btn-revoke{
    background-color: #fdeaea;
    border-color: #f3c2c2;
    color: #a11;
}
.btn-revoke:hover{ background-color: #fbdada; }
.btn-back{
    background: none;
    border: none;
    color: #4a5568;
    text-decoration: underline;
}
</style>
<div class="products-card">
<h2>Review Artisan: <?php echo htmlspecialchars($artisan['full_name']); ?></h2>

<div class="detail-row"><strong>Username</strong> <?php echo htmlspecialchars($artisan['username']); ?></div>
<div class="detail-row"><strong>Email</strong> <?php echo htmlspecialchars($artisan['email']); ?></div>
<div class="detail-row"><strong>Phone</strong> <?php echo htmlspecialchars($artisan['phone_number']); ?></div>
<div class="detail-row"><strong>Location</strong> <?php echo htmlspecialchars($artisan['location']); ?></div>
<div class="detail-row"><strong>Business Name</strong> <?php echo htmlspecialchars($artisan['business_name']); ?></div>
<div class="detail-row"><strong>ID Number</strong> <?php echo htmlspecialchars($artisan['id_number']); ?></div>
<div class="detail-row"><strong>KRA Pin</strong> <?php echo htmlspecialchars($artisan['kra']); ?></div>
<div class="detail-row"><strong>Account Status</strong> <?php echo htmlspecialchars($artisan['status']); ?></div>
<div class="detail-row"><strong>Approval Status</strong> <?php echo $artisan['approved'] == 1 ? 'Approved' : 'Pending review'; ?></div>

<h3>Submitted Documents</h3>
<ul class="doc-list">
    <li>Business Certificate:
        <?php if (!empty($artisan['business_certificate'])): ?>
            <a href="../Artisans/images/<?php echo htmlspecialchars($artisan['business_certificate']); ?>" target="_blank">View file</a>
        <?php else: ?>
            <span style="color:#a11;">Not uploaded</span>
        <?php endif; ?>
    </li>
    <li>Business Permit:
        <?php if (!empty($artisan['business_permit'])): ?>
            <a href="../Artisans/images/<?php echo htmlspecialchars($artisan['business_permit']); ?>" target="_blank">View file</a>
        <?php else: ?>
            <span style="color:#a11;">Not uploaded</span>
        <?php endif; ?>
    </li>
    <li>KEBS Certificate:
        <?php if (!empty($artisan['kebs'])): ?>
            <a href="../Artisans/images/<?php echo htmlspecialchars($artisan['kebs']); ?>" target="_blank">View file</a>
        <?php else: ?>
            <span style="color:#a11;">Not uploaded</span>
        <?php endif; ?>
    </li>
</ul>

<div class="review-actions">
<?php if($artisan['approved'] !=1): ?>
    <a href="./index.php?approve_artisan=<?php echo (int) $artisan['artisan_id'];?>"
        class="btn-approve"
        onclick="return confirm('Approve this artisan? Confirm you have reviewed their documents.');">
        Approve Artisan
    </a>
    <?php else:?>
    <a href="./index.php?revoke_artisan=<?php echo (int) $artisan['artisan_id']; ?>"
        class="btn-revoke"
        onclick="return confirm('Revoke this artisan\'s approval?');">
        Revoke Approval
    </a>
<?php endif;?>
<a href="./index.php?view_artisans" class="btn-back">Back to Artisans list</a>
</div>
</div>