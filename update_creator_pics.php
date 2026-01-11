<?php
include 'db.php';

echo "Finding creator for course 4...\n";
$course = mysqli_query($conn, "SELECT creator_id FROM courses WHERE id = 4");
$row = mysqli_fetch_assoc($course);
echo "Course 4 creator ID: " . $row['creator_id'] . "\n";

// Update creator profile pictures for existing files
$creator_5_pic = "uploads/creator_pictures/creator_5_1757214178.png";
$creator_6_pic = "uploads/creator_pictures/creator_6_1757230050.jpeg";

if (file_exists($creator_5_pic)) {
    $update_5 = "UPDATE creators SET profile_picture = '$creator_5_pic' WHERE id = 5";
    if (mysqli_query($conn, $update_5)) {
        echo "Updated creator 5 profile picture\n";
    }
}

if (file_exists($creator_6_pic)) {
    $update_6 = "UPDATE creators SET profile_picture = '$creator_6_pic' WHERE id = 6";
    if (mysqli_query($conn, $update_6)) {
        echo "Updated creator 6 profile picture\n";
    }
}

echo "Profile picture updates complete.\n";
?>
