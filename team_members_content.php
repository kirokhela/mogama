<div class="container">
  <h1>أعضاء فريق <?= htmlspecialchars($team) ?></h1>

  <div class="actions">
    <a href="dashboard.php" class="btn">⬅ رجوع للرئيسية</a>
    <a href="team_members.php?team=<?= urlencode($team) ?>&download_csv=1" class="btn">📥 تحميل CSV</a>
  </div>

  <!-- Filters -->
  <form method="get" class="filters">
    <input type="hidden" name="team" value="<?= htmlspecialchars($team) ?>">
    <input type="text" name="name" placeholder="🔍 بحث بالاسم" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
    <select name="is_case">
      <option value="">هل حالة؟</option>
      <option value="1" <?= (($_GET['is_case'] ?? '') === '1') ? 'selected' : '' ?>>نعم</option>
      <option value="0" <?= (($_GET['is_case'] ?? '') === '0') ? 'selected' : '' ?>>لا</option>
    </select>
    <button type="submit">تصفية</button>
  </form>

  <!-- Members Table -->
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
        <?php $counter = 1; ?>
        <?php while ($row = $members->fetch_assoc()): ?>
          <tr>
            <td><?= $counter++ ?></td>
            <?php foreach ($cols as $col): ?>
              <td>
                <?php 
                  if ($col === 'IsCase') {
                    echo $row[$col] == 1 ? "نعم" : "لا";
                  } else {
                    echo htmlspecialchars($row[$col]);
                  }
                ?>
              </td>
            <?php endforeach; ?>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>