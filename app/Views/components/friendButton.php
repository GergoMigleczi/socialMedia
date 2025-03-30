
    <?php switch ($status): 
        case 'Friends': ?>
            <button class="friend-action btn btn-secondary mx-1" data-action="Unfriend" data-profile-id="<?= $profileId ?>" style="display: <?=$display?>;">
                Unfriend
            </button>
            <?php break; 
        
        case 'Sent': ?>
            <button class="friend-action btn btn-secondary mx-1" data-action="Cancel" data-profile-id="<?= $profileId ?>" style="display: <?=$display?>;">
                Cancel Friend Request
            </button>
            <?php break; 
        
        case 'Received': ?>
            <span style="display: <?=$display?>;">
                <button class="friend-action btn btn-success mx-1" data-action="Accept" data-profile-id="<?= $profileId ?>" >
                    Accept Friend Request
                </button>
                <button class="friend-action btn btn-danger mx-1" data-action="Deny" data-profile-id="<?= $profileId ?>">
                    Deny Friend Request
                </button>
            </span>
            <?php break; 
        
        default: ?>
            <button class="friend-action btn btn-primary mx-1" data-action="Send" data-profile-id="<?= $profileId ?>" style="display: <?=$display?>;">
                Add Friend
            </button>
            <?php break; 
    endswitch; ?>
