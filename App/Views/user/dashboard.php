<?php
if (!isset($_SESSION)) {
    session_start();
}

ob_start();
?>
<div class="container mt-4">
    <h1 class="text-center">Dashboard</h1>
    <hr class="my-4">
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-12">
            <p class="text-center">Welcome to your dashboard <strong><?php echo htmlspecialchars($user['name']) ?></strong> !</p>
            <h2>Your Projects</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Project Title</th>
                        <th>Description</th>
                        <th>Goal Amount</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($userProjects)): ?>
                        <?php foreach ($userProjects as $project): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                <td><?php echo htmlspecialchars($project['description']); ?></td>
                                <td><?php echo htmlspecialchars($project['goal_amount']); ?> €</td>
                                <td>
                                    <a href="/php/PHPCrowFundingApp/public/index.php?action=detailsProject&id=<?php echo $project['id']; ?>" class="btn btn-primary btn-sm">Details</a>
                                    <a href="/php/PHPCrowFundingApp/public/index.php?action=editProject&project_id=<?php echo $project['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="/php/PHPCrowFundingApp/public/index.php?action=deleteProject&project_id=<?php echo $project['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No projects found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <h2>Your Donations</h2>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Project Title</th>
                        <th>Goal Amount</th>
                        <th>Donation Amount</th>
                        <th>Total Donations</th>
                        <th>Percentage Remaining</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($userDonations)): ?>
                        <?php foreach ($userDonations as $donation): ?>
                            <?php $project = $donationProjects[$donation['project_id']]; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($project['title']); ?></td>
                                <td><?php echo htmlspecialchars($project['goal_amount']); ?></td>
                                <td><?php echo htmlspecialchars($donation['amount']); ?> €</td>
                                <td><?php echo htmlspecialchars($project['total_donations']); ?> €</td>
                                <td><?php echo htmlspecialchars($project['percentage_remaining']); ?> %</td>
                                <td>
                                    <a href="/php/PHPCrowFundingApp/public/index.php?action=editDonation&id=<?php echo $donation['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="/php/PHPCrowFundingApp/public/index.php?action=deleteDonation&id=<?php echo $donation['id']; ?>" class="btn btn-danger btn-sm">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="1" class="text-right"><strong>Total Invested: <?php echo htmlspecialchars($totalInvested); ?> €</strong></td>
                        </tr>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No donations found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include __DIR__ . '/../layout.php';
?>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
