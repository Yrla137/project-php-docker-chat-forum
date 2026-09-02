<?php

    require_once 'includes/database.php';
    require_once 'includes/auth.php';
    require_once 'includes/group-membership.php';

    requireLogin();

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $application_id = $_POST['application_id'] ?? null;

        if(!$application_id){
            echo "Application ID is required.";
            exit();
        }

        // Fetch the application details
        $sql = "SELECT id, user_id, group_id, status FROM applications WHERE id = :application_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':application_id' => $application_id]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$application){
            echo "Application not found.";
            exit();
        }

        if($application['status'] !== 'pending'){
            echo "This application has already been processed.";
            exit();
        }

        // Check if the logged-in user is an administrator of the group
        $membership = getGroupMembership($pdo, $application['group_id'], getUserId());

        if(!$membership || $membership['role_name'] !== 'administrator'){
            echo "You do not have permission to approve applications for this group.";
            exit();
        }

        try{
            // Start transaction
            $pdo->beginTransaction();

            // Insert into group_members table
            $sql = "INSERT INTO group_members (group_id, user_id, role_id) VALUES (:group_id, :user_id, (SELECT id FROM group_roles WHERE name = 'member'))";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':group_id' => $application['group_id'],
                ':user_id' => $application['user_id']
            ]);

            // Update application status to 'approved'
            $sql = "UPDATE applications SET status = 'approved' WHERE id = :application_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':application_id' => $application_id]);

            // Commit transaction
            $pdo->commit();
            header("Location: applications.php?group_id=" . $application['group_id'] . "&action=approved");
            exit();
        
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            echo "Error: " . $e->getMessage();
            exit();
        }
    }