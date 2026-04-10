<?php
// Mock Data for Delete Account Requests
$requests = [
    ['id' => '1', 'name' => 'James Adair', 'avatar' => 'https://ui-avatars.com/api/?name=James+Adair&background=random', 'req_date' => '22 Apr 2025', 'del_date' => '30 Apr 2025', 'status' => 'Available'],
    ['id' => '2', 'name' => 'Adam Milne', 'avatar' => 'https://ui-avatars.com/api/?name=Adam+Milne&background=random', 'req_date' => '12 Apr 2025', 'del_date' => '15 Apr 2025', 'status' => 'Available'],
    ['id' => '3', 'name' => 'Richard Clark', 'avatar' => 'https://ui-avatars.com/api/?name=Richard+Clark&background=random', 'req_date' => '01 Apr 2025', 'del_date' => '02 Apr 2025', 'status' => 'Unavailable'],
    ['id' => '4', 'name' => 'Robert Reid', 'avatar' => 'https://ui-avatars.com/api/?name=Robert+Reid&background=random', 'req_date' => '05 Mar 2025', 'del_date' => '12 Mar 2025', 'status' => 'Available'],
    ['id' => '5', 'name' => 'Dottie Jeny', 'avatar' => 'https://ui-avatars.com/api/?name=Dottie+Jeny&background=random', 'req_date' => '20 Mar 2025', 'del_date' => '27 Mar 2025', 'status' => 'Available'],
    ['id' => '6', 'name' => 'Cheryl Bilodeau', 'avatar' => 'https://ui-avatars.com/api/?name=Cheryl+Bilodeau&background=random', 'req_date' => '01 Mar 2025', 'del_date' => '05 Mar 2025', 'status' => 'Available'],
];
?>

<link rel="stylesheet" href="../assets/css/delete_requests.css">

<div class="content delete-requests-layout">
    <div class="page-header" style="margin-bottom: 20px;">
        <h1 class="page-title">Delete Account Request</h1>
    </div>

    <!-- Filters -->
    <div class="filter-controls">
        <div class="search-wrap">
            <input type="text" placeholder="Search" class="form-control" style="max-width: 200px; padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 4px;">
        </div>
        <div class="sort-wrap">
            <select class="form-control" style="padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 4px; background: white;">
                <option>Sort By : Recent</option>
                <option>Sort By : Oldest</option>
            </select>
        </div>
    </div>

    <!-- Data Table -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="report-table del-table">
                <thead>
                    <tr>
                        <th style="text-align: left; padding-left: 20px;">User</th>
                        <th>Requisition Date</th>
                        <th>Delete Request Date</th>
                        <th>Status</th>
                        <th style="padding-right: 20px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <tr>
                            <td style="text-align: left; padding-left: 20px;">
                                <div class="user-cell">
                                    <img src="<?php echo $req['avatar']; ?>" alt="Avatar">
                                    <span class="user-name"><?php echo htmlspecialchars($req['name']); ?></span>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($req['req_date']); ?></td>
                            <td><?php echo htmlspecialchars($req['del_date']); ?></td>
                            <td>
                                <?php if ($req['status'] == 'Available'): ?>
                                    <span class="status-badge border-green">Available</span>
                                <?php else: ?>
                                    <span class="status-badge border-red">Unavailable</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; padding-right: 20px;">
                                <button class="btn-options" onclick="Swal.fire('Manage Request','Pending Backend Sync','info')"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <!-- Footer Pagination Mock -->
        <div class="table-footer">
            <div class="rows-per-page">
                Row Per Page 
                <select>
                    <option>10</option>
                    <option>20</option>
                </select>
                Entries
            </div>
            <div class="pagination">
                <button class="page-btn"><i class="fa-solid fa-arrow-left"></i></button>
                <button class="page-btn active-page">1</button>
                <button class="page-btn"><i class="fa-solid fa-arrow-right"></i></button>
            </div>
        </div>
    </div>
</div>
