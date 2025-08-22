<h1>أعضاء فريق <?= htmlspecialchars($team) ?></h1>

<div class="cards">
    <a href="dashboard.php" class="btn">⬅ رجوع للرئيسية</a>
    <a href="team_members.php?team=<?= urlencode($team) ?>&download_csv=1" class="btn">📥 تحميل CSV</a>
</div>

<div class="summary">
    <p>إجمالي الأعضاء: <strong><?= $total_rows ?></strong></p>
    <p>إجمالي المدفوعات: <strong><?= number_format($total_payment, 2) ?></strong></p>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <?php foreach ($cols as $col): ?>
                    <th><?= htmlspecialchars($col) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $counter = $offset + 1; ?>
            <?php foreach ($members as $row): ?>
            <tr>
                <td><?= $counter++ ?></td>
                <?php foreach ($cols as $col): ?>
                    <td>
                        <?php 
                            if ($col === 'IsCase') {
                                echo $row[$col] == 1 ? "Yes" : "No";
                            } else {
                                echo htmlspecialchars($row[$col]);
                            }
                        ?>
                    </td>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<div class="pagination">
    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?team=<?= urlencode($team) ?>&page=<?= $i ?>" 
           class="<?= ($i == $page) ? 'active' : '' ?>">
            <?= $i ?>
        </a>
    <?php endfor; ?>
</div>
<?php endif; ?>