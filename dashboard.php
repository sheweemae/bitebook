  <?php
    session_start();
    if ((!isset($_SESSION['user_email'])) && !isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $user_email = $_SESSION['user_email'];
    $user_id = $_SESSION['user_id'];

    $host = 'localhost';
    $username = "root";
    $password = "";

    // Connect to recipe_list
    $db_recipe_list = "bitebook_recipe_list";
    $conn_list = mysqli_connect($host, $username, $password, $db_recipe_list);
    if (!$conn_list) die("Connection failed (list): " . mysqli_connect_error());

    // Connect to recipe_info
    $db_recipe_info = "bitebook_recipe_info";
    $conn_info = mysqli_connect($host, $username, $password, $db_recipe_info);
    if (!$conn_info) die("Connection failed (info): " . mysqli_connect_error());

    if (isset($_POST['ajax_update_password'])) {
        if (!isset($_SESSION['user_id'])) { echo "Not logged in"; exit; }
        $user_id = $_SESSION['user_id'];
        $new_password = $_POST['ajax_update_password'];
        if (strlen($new_password) < 6) { echo "Password too short"; exit; }
        $hash = password_hash($new_password, PASSWORD_DEFAULT);

        $host = 'localhost';
        $username = "root";
        $password = "";
        $db = "bitebook_users";
        $conn = mysqli_connect($host, $username, $password, $db);
        if (!$conn) { echo "DB error"; exit; }

        $sql = "UPDATE users SET password='$hash' WHERE user_id='$user_id'";
        if (mysqli_query($conn, $sql)) {
            echo "ok";
        } else {
            echo "DB update failed";
        }
        mysqli_close($conn);
        exit;
    }

    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    $user_recipes = [];
    if ($filter === 'all' || $filter === '') {
        $sql = "SELECT r.recipe_id, r.recipe_name, r.imgfile, r.is_favorite, i.servings, i.cooking_time, i.prep_time
                FROM recipes r
                LEFT JOIN bitebook_recipe_info.recipe_info i ON r.recipe_id = i.recipe_id
                WHERE r.user_id = '$user_id'";
    } elseif ($filter === 'favorites') {
        $sql = "SELECT r.recipe_id, r.recipe_name, r.imgfile, r.is_favorite, i.servings, i.cooking_time, i.prep_time
                FROM recipes r
                LEFT JOIN bitebook_recipe_info.recipe_info i ON r.recipe_id = i.recipe_id
                WHERE r.user_id = '$user_id' AND r.is_favorite = 1";
    } else {
        $sql = "SELECT r.recipe_id, r.recipe_name, r.imgfile, r.is_favorite, i.servings, i.cooking_time, i.prep_time
                FROM recipes r
                LEFT JOIN bitebook_recipe_info.recipe_info i ON r.recipe_id = i.recipe_id
                WHERE r.user_id = '$user_id'
                AND r.recipe_id IN (
                    SELECT recipe_id FROM recipe_attribute_values v
                    JOIN recipe_attributes a ON v.attribute_id = a.attribute_id
                    WHERE a.attribute_name = '$filter'
                )";
    }
    $result = mysqli_query($conn_list, $sql);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $user_recipes[] = $row;
        }
    }

    function get_attribute_id($conn, $name) {
        $name = mysqli_real_escape_string($conn, $name);
        $res = mysqli_query($conn, "SELECT attribute_id FROM recipe_attributes WHERE attribute_name='$name' LIMIT 1");
        if ($row = mysqli_fetch_assoc($res)) return $row['attribute_id'];
        return null;
    }

    if (isset($_POST['delete_recipe_id'])) {
        $rid = intval($_POST['delete_recipe_id']);
        // Delete from all related tables 
        mysqli_query($conn_list, "DELETE FROM recipes WHERE recipe_id='$rid' AND user_id='$user_id'");
        mysqli_query($conn_info, "DELETE FROM recipe_info WHERE recipe_id='$rid'");
        mysqli_query($conn_info, "DELETE FROM ingredients WHERE recipe_id='$rid'");
        mysqli_query($conn_info, "DELETE FROM instructions WHERE recipe_id='$rid'");
        mysqli_query($conn_list, "DELETE FROM recipe_attribute_values WHERE recipe_id='$rid'");
        echo "ok";
        exit;
    }

    if (isset($_POST['edit_recipe_id'])) {
        $rid = intval($_POST['edit_recipe_id']);
        // Update recipe fields
        $title = mysqli_real_escape_string($conn_list, $_POST['title']);
        $servings = mysqli_real_escape_string($conn_info, $_POST['servings']);
        $prep_hour = (int)$_POST['prep_hour'];
        $prep_minute = (int)$_POST['prep_minute'];
        $cook_hour = (int)$_POST['cook_hour'];
        $cook_minute = (int)$_POST['cook_minute'];
        $prep_time = sprintf('%02d:%02d:00', $prep_hour, $prep_minute);
        $cook_time = sprintf('%02d:%02d:00', $cook_hour, $cook_minute);

        // Update image 
        $image_path = null;
        if (isset($_FILES['mainPhoto']) && $_FILES['mainPhoto']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['mainPhoto']['tmp_name'];
            $fileName = $_FILES['mainPhoto']['name'];
            move_uploaded_file($fileTmpPath, "uploads/" . $fileName);
            $image_path = "uploads/" . $fileName;
            mysqli_query($conn_list, "UPDATE recipes SET imgfile='$image_path' WHERE recipe_id='$rid' AND user_id='$user_id'");
        }

        mysqli_query($conn_list, "UPDATE recipes SET recipe_name='$title' WHERE recipe_id='$rid' AND user_id='$user_id'");
        mysqli_query($conn_info, "UPDATE recipe_info SET servings='$servings', prep_time='$prep_time', cooking_time='$cook_time' WHERE recipe_id='$rid'");

        // Update ingredients
        mysqli_query($conn_info, "DELETE FROM ingredients WHERE recipe_id='$rid'");
        foreach ($_POST['ingredients'] as $ingredient) {
            $ingredient = mysqli_real_escape_string($conn_info, $ingredient);
            if (trim($ingredient) !== '') {
                mysqli_query($conn_info, "INSERT INTO ingredients (recipe_id, ingredient_name) VALUES ('$rid', '$ingredient')");
            }
        }

        // Update instructions
        mysqli_query($conn_info, "DELETE FROM instructions WHERE recipe_id='$rid'");
        $step = 1;
        foreach ($_POST['instructions'] as $instruction) {
            $instruction = mysqli_real_escape_string($conn_info, $instruction);
            if (trim($instruction) !== '') {
                mysqli_query($conn_info, "INSERT INTO instructions (recipe_id, step_number, instruction_text) VALUES ('$rid', '$step', '$instruction')");
                $step++;
            }
        }

        // Update attributes
        mysqli_query($conn_list, "DELETE FROM recipe_attribute_values WHERE recipe_id='$rid'");
        if (!empty($_POST['tags'])) {
            $tags = explode(',', $_POST['tags']);
            foreach ($tags as $tag) {
                $attr_id = get_attribute_id($conn_list, $tag);
                if ($attr_id) {
                    mysqli_query($conn_list, "INSERT INTO recipe_attribute_values (recipe_id, attribute_id) VALUES ('$rid', '$attr_id')");
                }
            }
        }
        if (!empty($_POST['times'])) {
            $times = explode(',', $_POST['times']);
            foreach ($times as $time) {
                $attr_id = get_attribute_id($conn_list, $time);
                if ($attr_id) {
                    mysqli_query($conn_list, "INSERT INTO recipe_attribute_values (recipe_id, attribute_id) VALUES ('$rid', '$attr_id')");
                }
            }
        }
        header("Location: dashboard.php?edited=1");
        exit;
    }

    // Save Recipe to Database
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save-rec-btn'])) {
        // --- Validate required fields ---
        $errors = [];

        // Title
        if (empty($_POST['title'])) {
            $errors[] = "Recipe title is required.";
        }

        // Ingredients (at least one non-empty)
        $hasIngredient = false;
        if (!empty($_POST['ingredients'])) {
            foreach ($_POST['ingredients'] as $ingredient) {
                if (trim($ingredient) !== '') {
                    $hasIngredient = true;
                    break;
                }
            }
        }
        if (!$hasIngredient) {
            $errors[] = "At least one ingredient is required.";
        }

        // Instructions (at least one non-empty)
        $hasInstruction = false;
        if (!empty($_POST['instructions'])) {
            foreach ($_POST['instructions'] as $instruction) {
                if (trim($instruction) !== '') {
                    $hasInstruction = true;
                    break;
                }
            }
        }
        if (!$hasInstruction) {
            $errors[] = "At least one instruction is required.";
        }

        // Servings
        if (empty($_POST['servings'])) {
            $errors[] = "Servings is required.";
        }

        // Cooking time
        if (empty($_POST['cook_hour']) || empty($_POST['cook_minute'])) {
            $errors[] = "Cooking time is required.";
        }

        // Prep time
        if (empty($_POST['prep_hour']) || empty($_POST['prep_minute'])) {
            $errors[] = "Prep time is required.";
        }

        // Tags
        if (empty($_POST['tags'])) {
            $errors[] = "At least one tag is required.";
        }
        // Times
        if (empty($_POST['times'])) {
            $errors[] = "At least one time of day is required.";
        }

        // Image
        if (!isset($_FILES['mainPhoto']) || $_FILES['mainPhoto']['error'] != UPLOAD_ERR_OK) {
            $errors[] = "Recipe photo is required.";
        }

        // proceed if there no errors
        if (empty($errors)) {
            //  file upload
            $image_path = null;
            if (isset($_FILES['mainPhoto']) && $_FILES['mainPhoto']['error'] == UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['mainPhoto']['tmp_name'];
                $fileName = $_FILES['mainPhoto']['name'];
                move_uploaded_file($fileTmpPath, "uploads/" . $fileName);
                $image_path = "uploads/" . $fileName;
            }

            // Insert into recipes (bitebook_recipe_list)
            $recipe_name = mysqli_real_escape_string($conn_list, $_POST['title']);
            $sql = "INSERT INTO recipes (user_id, recipe_name, imgfile) VALUES ('$user_id', '$recipe_name', '$image_path')";
            mysqli_query($conn_list, $sql);
            $recipe_id = mysqli_insert_id($conn_list);

            // Insert into recipe_info (bitebook_recipe_info) 
            $prep_hour = (int)$_POST['prep_hour'];
            $prep_minute = (int)$_POST['prep_minute'];
            $cook_hour = (int)$_POST['cook_hour'];
            $cook_minute = (int)$_POST['cook_minute'];

            $prep_time = sprintf('%02d:%02d:00', $prep_hour, $prep_minute);
            $cook_time = sprintf('%02d:%02d:00', $cook_hour, $cook_minute);
            $servings = mysqli_real_escape_string($conn_info, $_POST['servings']);

            $sql_info = "INSERT INTO recipe_info (recipe_id, cooking_time, prep_time, servings) VALUES ('$recipe_id', '$cook_time', '$prep_time', '$servings')";
            mysqli_query($conn_info, $sql_info);

            // Insert ingredients (bitebook_recipe_info.ingredients)
            foreach ($_POST['ingredients'] as $ingredient) {
                $ingredient = mysqli_real_escape_string($conn_info, $ingredient);
                if (trim($ingredient) !== '') {
                    $sql_ing = "INSERT INTO ingredients (recipe_id, ingredient_name) VALUES ('$recipe_id', '$ingredient')";
                    mysqli_query($conn_info, $sql_ing);
                }
            }

            // Insert instructions (bitebook_recipe_info.instructions) 
            $step = 1;
            foreach ($_POST['instructions'] as $instruction) {
                $instruction = mysqli_real_escape_string($conn_info, $instruction);
                if (trim($instruction) !== '') {
                    $sql_inst = "INSERT INTO instructions (recipe_id, step_number, instruction_text) VALUES ('$recipe_id', '$step', '$instruction')";
                    mysqli_query($conn_info, $sql_inst);
                    $step++;
                }
            }

            // Tags (categories)
            if (!empty($_POST['tags'])) {
                $tags = explode(',', $_POST['tags']);
                foreach ($tags as $tag) {
                    $attr_id = get_attribute_id($conn_list, $tag);
                    if ($attr_id) {
                        mysqli_query($conn_list, "INSERT INTO recipe_attribute_values (recipe_id, attribute_id) VALUES ('$recipe_id', '$attr_id')");
                    }
                }
            }

            // Times (time of day)
            if (!empty($_POST['times'])) {
                $times = explode(',', $_POST['times']);
                foreach ($times as $time) {
                    $attr_id = get_attribute_id($conn_list, $time);
                    if ($attr_id) {
                        mysqli_query($conn_list, "INSERT INTO recipe_attribute_values (recipe_id, attribute_id) VALUES ('$recipe_id', '$attr_id')");
                    }
                }
            }

            $recipesaved = true;
        } else {
            $recipesaved = false;
        }
    }


    function timeToMinutes($timeStr) {
        if (!$timeStr) return 0;
        $parts = explode(':', $timeStr);
        $h = isset($parts[0]) ? (int)$parts[0] : 0;
        $m = isset($parts[1]) ? (int)$parts[1] : 0;
        $s = isset($parts[2]) ? (int)$parts[2] : 0; 
        return ($h * 60) + $m;
    }
    function getPrepType($prep_time) {
        $prep_minutes = timeToMinutes($prep_time);
        if ($prep_minutes <= 30) {
            return "EASY PREP";
        } elseif ($prep_minutes <= 60) {
            return "MEDIUM PREP";
        } else {
            return "HARD PREP";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_recipe']) && $_POST['cancel_recipe'] == "1") {
    header("Location: dashboard.php");
    exit; 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BiteBook Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-layout">
        <div class="sidebar">
            <div class="logo">
                <img src="images/text/bitebook-navbar.svg" alt="Bitebook" class="main-title">
            </div>
            <div class="section-title">Home</div>
            <a href="dashboard.php?filter=all" class="nav-link<?php if($filter=='all') echo ' active'; ?>" id="myRecipesBtn">
                <span class="icon">🍽️</span> My Recipes
            </a>
            <a href="dashboard.php?filter=favorites" class="nav-link<?php if($filter=='favorites') echo ' active'; ?>" id="favoritesBtn">
                <span class="icon">💙</span> Favorites
            </a>
            <a href="dashboard.php?filter=Quick" class="category-link<?php if($filter=='Quick') echo ' active'; ?>" data-filter="Quick"><span class="icon">⚡</span>Quick</a>
            <a href="dashboard.php?filter=Healthy" class="category-link<?php if($filter=='Healthy') echo ' active'; ?>" data-filter="Healthy"><span class="icon">🥗</span>Healthy</a>
            <a href="dashboard.php?filter=Sweet" class="category-link<?php if($filter=='Sweet') echo ' active'; ?>" data-filter="Sweet"><span class="icon">🍬</span> Sweet</a>
            <a href="dashboard.php?filter=Spicy" class="category-link<?php if($filter=='Spicy') echo ' active'; ?>" data-filter="Spicy"><span class="icon">🌶️</span> Spicy</a>
            <a href="dashboard.php?filter=Comfort" class="category-link<?php if($filter=='Comfort') echo ' active'; ?>" data-filter="Comfort"><span class="icon">💤</span> Comfort</a>
            <hr>
            <div class="section-title">Time of the Day</div>
            <a href="dashboard.php?filter=Breakfast" class="nav-link<?php if($filter=='Breakfast') echo ' active'; ?>" data-filter="Breakfast"><span class="icon"><span class="dot breakfast"></span></span> Breakfast</a>
            <a href="dashboard.php?filter=Lunch" class="nav-link<?php if($filter=='Lunch') echo ' active'; ?>" data-filter="Lunch"><span class="icon"><span class="dot lunch"></span></span> Lunch</a>
            <a href="dashboard.php?filter=Dinner" class="nav-link<?php if($filter=='Dinner') echo ' active'; ?>" data-filter="Dinner"><span class="icon"><span class="dot dinner"></span></span> Dinner</a>
            <a href="dashboard.php?filter=Snacks%20%26%20Dessert" class="nav-link<?php if($filter=='Snacks & Dessert') echo ' active'; ?>" data-filter="Snacks & Dessert"><span class="icon"><span class="dot snack"></span></span> Snack & Dessert</a>
            <a href="dashboard.php?filter=Beverages" class="nav-link<?php if($filter=='Beverages') echo ' active'; ?>" data-filter="Beverages"><span class="icon"><span class="dot beverages"></span></span> Beverages</a>
            <a href="#" id="createRecipeBtn" class="create-recipe-btn">
                <span class="createNewRecipe">&#43;</span>
                Create New Recipe
            </a>
        </div>
        <div class="content-area">
            <div class="topbar">
                <button class="ingredient-btn">🍲 Ingredient Matching</button>
                <input type="text" class="search-bar" placeholder="Search...">
                <div class="topbar-actions">
                    <button class="profile-btn">👤 View Profile</button>
                    <button type="submit" class="logout-btn" name="submit_logout" value="1">🚪 Log Out</button>
                </div>
            </div>
            <div class="main-content" id="mainContent">
              <div class="main-title-content">
                  <?php
                  if ($filter == 'all') {
                      echo '<p>my recipes</p>';
                  } elseif ($filter == 'favorites') {
                      echo '<p>favorites</p>';
                  } else {
                      $smallfilter = strtolower($filter);
                      echo '<p>' . htmlspecialchars($smallfilter) . '</p>';
                  }
                  ?>
              </div>
            <div class="recipe-grid">
                <?php if (empty($user_recipes)): ?>
                <div class="empty-grid">No recipes found. Click "Create New Recipe" to add one!</div>
                <?php else: ?>
                <?php foreach ($user_recipes as $recipe): ?>
                <div class="recipe-card">
                    <div class="recipe-img">
                        <img src="<?php echo htmlspecialchars($recipe['imgfile']); ?>" alt="Recipe Image">                    
                    </div>
                    <div class="recipe-body">
                        <div class="recipe-title"><?php echo htmlspecialchars($recipe['recipe_name']); ?></div>
                        <div class="recipe-fav">
                            <span class="recipe-heart<?php if(isset($recipe['is_favorite']) && $recipe['is_favorite']) echo ' filled'; ?>"
                                data-recipe-id="<?php echo $recipe['recipe_id']; ?>">
                                <?php echo (isset($recipe['is_favorite']) && $recipe['is_favorite']) ? '♥' : '♡'; ?>
                            </span>
                        </div>
                        <div class="recipe-info">
                            <?php
                            $total_minutes = timeToMinutes($recipe['prep_time']) + timeToMinutes($recipe['cooking_time']);
                            $total_time_str = sprintf('%02d:%02d:00', floor($total_minutes / 60), $total_minutes % 60);
                            ?>

                            <span>⏱️<?php echo $total_minutes ?> MIN</span>
                            <span>• 
                              <?php
                            $preptype = getPrepType($total_time_str);
                            echo $preptype;
                            ?>
                            </span>
                            <span>• <?php echo htmlspecialchars($recipe['servings']); ?> Servings</span>
                        </div>
                        <div class="recipe-tags">
                            <?php
                            $sql = "SELECT attribute_name FROM recipe_attributes WHERE attribute_id IN (SELECT attribute_id FROM recipe_attribute_values WHERE recipe_id = '{$recipe['recipe_id']}')";
                            $result = mysqli_query($conn_list, $sql);
                            $attributes = [];
                            while ($row = mysqli_fetch_assoc($result)) {
                                $attributes[] = $row['attribute_name'];
                            }

                            $dotClasses = [
                                'Breakfast' => 'breakfast',
                                'Lunch' => 'lunch',
                                'Dinner' => 'dinner',
                                'Snacks & Dessert' => 'snack',
                                'Beverages' => 'beverages'
                            ];

                            foreach ($dotClasses as $attrName => $dotClass) {
                                if (in_array($attrName, $attributes)) {
                                    echo '<span class="dot ' . $dotClass . '"></span>';
                                }
                            }
                            ?>
                        </div>
                        <button class="view-btn">VIEW RECIPE</button>
                    </div>
                </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
<script>
const recipes = <?php
$jsRecipes = [];
foreach ($user_recipes as $recipe) {
    // Fetch ingredients
    $ings = [];
    $res = mysqli_query($conn_info, "SELECT ingredient_name FROM ingredients WHERE recipe_id = '{$recipe['recipe_id']}'");
    while ($row = mysqli_fetch_assoc($res)) $ings[] = $row['ingredient_name'];

    // Fetch instructions
    $insts = [];
    $res = mysqli_query($conn_info, "SELECT instruction_text FROM instructions WHERE recipe_id = '{$recipe['recipe_id']}' ORDER BY step_number ASC");
    while ($row = mysqli_fetch_assoc($res)) $insts[] = $row['instruction_text'];

    // Fetch attributes (categories and times)
    $categories = [];
    $timesOfDay = [];
    $res = mysqli_query($conn_list, "SELECT a.attribute_name FROM recipe_attribute_values v JOIN recipe_attributes a ON v.attribute_id = a.attribute_id WHERE v.recipe_id = '{$recipe['recipe_id']}'");
    while ($row = mysqli_fetch_assoc($res)) {
        $attr = $row['attribute_name'];
        if (in_array($attr, ['Quick', 'Healthy', 'Sweet', 'Spicy', 'Comfort'])) {
            $categories[] = $attr;
        } else if (in_array($attr, ['Breakfast', 'Lunch', 'Dinner', 'Snacks & Dessert', 'Beverages'])) {
            $timesOfDay[] = $attr;
        }
    }

    $jsRecipes[] = [
        'id' => $recipe['recipe_id'],
        'title' => $recipe['recipe_name'],
        'image' => $recipe['imgfile'],
    'is_favorite' => (int)$recipe['is_favorite'], 
        'servings' => $recipe['servings'],
        'prep_time' => $recipe['prep_time'],
        'cook_time' => $recipe['cooking_time'],
        'total_time_str' => $total_time_str,
        'prep_type' => getPrepType($total_time_str),
        'categories' => $categories,
        'timesOfDay' => $timesOfDay,
        'ingredients' => $ings,
        'instructions' => $insts
    ];
}
echo json_encode($jsRecipes, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP);
?>;
</script>

<script>
<?php if (isset($_GET['edited']) && $_GET['edited'] == 1): ?>
Swal.fire({
    title: 'Recipe Edited!',
    text: 'Your recipe has been updated successfully.',
    icon: 'success',
    confirmButtonText: 'OK'
}).then(() => {
    window.location.href = 'dashboard.php';
});
<?php endif; ?>
  
<?php if (isset($recipesaved) && !$recipesaved && !empty($errors)): ?>
Swal.fire({
    title: 'Error!',
    html: '<?php echo implode("<br>", array_map("htmlspecialchars", $errors)); ?>',
    icon: 'error',
    confirmButtonText: 'OK'
});
<?php endif; ?>

<?php if (isset($recipesaved) && $recipesaved): ?>
Swal.fire({
    title: 'Recipe Saved!',
    text: 'Your recipe has been saved successfully.',
    icon: 'success',
    confirmButtonText: 'OK'
}).then(() => {
    window.location.href = 'dashboard.php';
});
<?php endif; ?>

function renderRecipeDetail(recipe) {
  // Category emojis
  const categoryEmojis = {
    'Quick': '⚡',
    'Healthy': '🥗',
    'Sweet': '🍬',
    'Spicy': '🌶️',
    'Comfort': '💤'
  };

  // categories with emojis
  const categoriesHTML = recipe.categories.map(cat =>
    `<span class="categories-display">${categoryEmojis[cat] || ''} ${cat}</span>`
  ).join(' ');

  // time of day dots
  const dotClasses = {
    'Breakfast': 'breakfast',
    'Lunch': 'lunch',
    'Dinner': 'dinner',
    'Snacks & Dessert': 'snack',
    'Beverages': 'beverages'
  };
  const timesHTML = recipe.timesOfDay.map(time =>
    `<span class="dot ${dotClasses[time] || ''}"></span> ${time}`
  ).join(' ');

  //  format time as MINS
  function formatTime(timeStr) {
    if (!timeStr || timeStr === "00:00" || timeStr === "00:00:00") return '0 MINS';
    const parts = timeStr.split(':');
    const h = parseInt(parts[0] || 0, 10);
    const m = parseInt(parts[1] || 0, 10);
    const total = h * 60 + m;
    return `${total} MINS`;
  }
  const prepTime = formatTime(recipe.prep_time);
  const cookTime = formatTime(recipe.cook_time);

  // Ingredients
  const ingredientsHTML = recipe.ingredients.map(i => `<li>${i}</li>`).join('');

  // Instructions: 
  const instructionsHTML = recipe.instructions.map((ins, i) => `<li><b>Step ${i+1}:</b> ${ins}</li>`).join('');

  // Favorite heart (clickable)
const heart = `<span id="detailHeart" class="recipe-heart${recipe.is_favorite ? ' filled' : ''}" data-recipe-id="${recipe.id}" style="cursor:pointer;${recipe.is_favorite ? 'color:#f43f5e;' : 'color:#bbb;'}">${recipe.is_favorite ? '♥' : '♡'}</span>`;  function timeToMinutes(timeStr) {
    if (!timeStr) return 0;
    const parts = timeStr.split(':');
    const h = parseInt(parts[0] || 0, 10);
    const m = parseInt(parts[1] || 0, 10);
    return h * 60 + m;
  }
  const totalTime = timeToMinutes(recipe.prep_time) + timeToMinutes(recipe.cook_time);

  return `
    <div class="recipe-detail-card" style="background:#fff;border-radius:18px;box-shadow:0 2px 16px rgba(0,0,0,0.06);padding:60px 60px;margin:32px 0; display: flex; gap: 40px;">
      <div style="flex: 1; padding-left: 0;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
          <h2 style="margin:0 0 15px 0;font-size:2rem;font-weight:700;">${recipe.title}</h2>
          ${heart}
        </div>
        <div style="margin-bottom:12px;">
          ${categoriesHTML}
        </div>
        <div style="margin-bottom:12px;">
          ${timesHTML}
        </div>
        <div style="display: flex; align-items: center; gap: 24px; margin-bottom: 24px; margin-top: 24px; margin-right: 30px;">
          <div style="flex: 1; text-align: left;">
            <div style="font-size: 0.85rem; color: #888; letter-spacing: 1px;">PREP TIME</div>
            <div style="font-size: 1.1rem; font-weight: 500; margin-top: 2px;">${prepTime}</div>
          </div>
          <div style="width: 1px; height: 40px; background: #ececec;"></div>
          <div style="flex: 1; text-align: left;">
            <div style="font-size: 0.85rem; color: #888; letter-spacing: 1px;">COOK TIME</div>
            <div style="font-size: 1.1rem; font-weight: 500; margin-top: 2px;">${cookTime}</div>
          </div>
          <div style="width: 1px; height: 40px; background: #ececec;"></div>
          <div style="flex: 1; text-align: left;">
            <div style="font-size: 0.85rem; color: #888; letter-spacing: 1px;">PREP TYPE</div>
            <div style="font-size: 1.1rem; font-weight: 500; margin-top: 2px;">${recipe.prep_type}</div>
          </div>
          <div style="width: 1px; height: 40px; background: #ececec;"></div>
          <div style="flex: 1; text-align: left;">
            <div style="font-size: 0.85rem; color: #888; letter-spacing: 1px;">SERVINGS</div>
            <div style="font-size: 1.1rem; font-weight: 500; margin-top: 2px;">${recipe.servings}</div>
          </div>
        </div>
        <div style="margin-bottom: 24px;">
          <button id="editRecipeBtn" style="margin-right:12px;background:#fff;border:1.5px solid #222;border-radius:8px;padding:8px 20px;font-size:1rem;font-weight:600;cursor:pointer;">Edit</button>
          <button id="deleteRecipeBtn" style="background:#fff;border:1.5px solid #d33;color:#d33;border-radius:8px;padding:8px 20px;font-size:1rem;font-weight:600;cursor:pointer;">Delete</button>
          <a href="#" id="backBtn" style="color:#222;font-size:0.95rem;text-decoration:underline;float:right;margin-left:16px;">Back</a>
        </div>
        <div style="display:flex;gap:40px;margin-top:32px;">
          <div style="flex:1;">
            <h3 style="margin-bottom:8px;">Ingredients</h3>
            <ul style="margin:0;padding-left:18px;">
              ${ingredientsHTML}
            </ul>
          </div>
          <div style="flex:1;">
            <h3 style="margin-bottom:8px;">Instructions</h3>
            <ul style="margin:0;padding-left:18px;">
              ${instructionsHTML}
            </ul>
          </div>
        </div>
      </div>
      <div style="flex: 1; max-width: 900px;">
        <img src="${recipe.image}" alt="Recipe Image" style="width:100%;height:100%;border-radius: 12px; object-fit: cover;">
      </div>
    </div>
  `;
}
    
// Heart toggle for recipe cards
document.querySelectorAll('.recipe-heart').forEach(heart => {
  heart.addEventListener('click', function (e) {
    e.stopPropagation();
    const recipeId = this.getAttribute('data-recipe-id');
    const isFilled = this.classList.contains('filled');
    const setFav = isFilled ? 0 : 1;

    fetch('toggle_favorite.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: `recipe_id=${recipeId}&set_fav=${setFav}`
    })
    .then(response => response.text())
    .then(data => {
      if (data.trim() === 'ok') {
        if (isFilled) {
          this.textContent = '♡';
          this.style.color = '';
          this.classList.remove('filled');
        } else {
          this.textContent = '♥';
          this.style.color = '#f43f5e';
          this.classList.add('filled');
        }
      } else {
        alert('Failed to update favorite!');
      }
    });
  });
});

document.querySelectorAll('.sidebar .nav-link, .sidebar .category-link').forEach(link => {
  link.addEventListener('click', function (e) {
    document.querySelectorAll('.sidebar .nav-link, .sidebar .category-link').forEach(l => l.classList.remove('active'));
    this.classList.add('active');
  });
});

const recipeFormHTML = `
  <form action="dashboard.php" method="post" enctype="multipart/form-data" id="recipeForm">
    <div class="form-container" style="
      background: #fff;
      border-radius: 18px;
      box-shadow: 0 2px 16px rgba(0,0,0,0.06);
      padding: 48px 48px 64px 48px;
      max-width: auto;
    ">
      <div style="display: flex; gap: 48px; align-items: flex-start;">
        <!-- Left: Image upload -->
        <div style="flex: 1; display: flex; flex-direction: column; align-items: flex-start;">
        <label for="mainPhoto" id="mainPhotoLabel" style="display:block;width:100%;aspect-ratio:1.2/1.0;background:#fafafd;border-radius:18px;display:flex;align-items:center;justify-content:center;border:1.5px dashed #e5e7eb;margin-bottom:24px; position: relative; overflow: hidden;cursor:pointer;">
            <span id="mainPhotoPlaceholder" style="font-size:3rem;color:#bdbdbd;">&#128247;</span>
            <input type="file" name="mainPhoto" id="mainPhoto" accept="image/*" style="display:none;">
        </label>
        <div style="text-align:center;width:100px;margin-top:8px;color:#888;">Add Photo</div>
        </div>

        <!-- Right: Form fields -->
        <div style="flex:2;">
          <h2 style="margin-top:0;">Create New Recipe</h2>
          <b>Recipe Title:</b><br>
          <input type="text" name="title" class="input" style="width:100%;margin-bottom:12px;">

          <div style="margin-bottom:12px;">
            <b>Ingredients:</b><br>
            <div id="ingredientsList">
              <input type="text" name="ingredients[]" class="input" style="width:100%;margin-bottom:6px;">
              <input type="text" name="ingredients[]" class="input" style="width:100%;margin-bottom:6px;">
              <input type="text" name="ingredients[]" class="input" style="width:100%;margin-bottom:6px;">
              <input type="text" name="ingredients[]" class="input" style="width:100%;margin-bottom:6px;">
            </div>
            <button type="button" id="addIngredient" style="background:none;border:none;color:#666;cursor:pointer;font-size:1rem;padding:0;margin-top:4px;">&#43; Add Ingredient</button>
          </div>

          <div style="margin-bottom:12px;">
            <b>Instructions:</b><br>
            <div id="instructionsList">
              <div style="display:flex;align-items:center;margin-bottom:6px;">
                <span style="width:24px;display:inline-block;">1</span>
                <input type="text" name="instructions[]" class="input" style="flex:1;">
              </div>
              <div style="display:flex;align-items:center;margin-bottom:6px;">
                <span style="width:24px;display:inline-block;">2</span>
                <input type="text" name="instructions[]" class="input" style="flex:1;">
              </div>
              <div style="display:flex;align-items:center;margin-bottom:6px;">
                <span style="width:24px;display:inline-block;">3</span>
                <input type="text" name="instructions[]" class="input" style="flex:1;">
              </div>
              <div style="display:flex;align-items:center;margin-bottom:6px;">
                <span style="width:24px;display:inline-block;">4</span>
                <input type="text" name="instructions[]" class="input" style="flex:1;">
              </div>
            </div>
            <button type="button" id="addInstruction" style="background:none;border:none;color:#666;cursor:pointer;font-size:1rem;padding:0;margin-top:4px;">&#43; Add Instruction</button>
          </div>

          <label>Servings<br>
            <input type="number" name="servings" class="input" style="width:150px;margin-bottom:12px;">
          </label>

          <div style="margin-bottom:12px;">
            <b>Cooking Time: (00:00)</b><br>
            <input type="number" name="cook_hour" class="input" style="width:100px;" placeholder="Hour">
            <input type="number" name="cook_minute" class="input" style="width:100px;" placeholder="Minute">
          </div>

          <div style="margin-bottom:12px;">
            <b>Prep Time: (00:00)</b><br>
            <input type="number" name="prep_hour" class="input" style="width:100px;" placeholder="Hour">
            <input type="number" name="prep_minute" class="input" style="width:100px;" placeholder="Minute">
          </div>

          <div style="margin-bottom:24px;">
            <b>Tags:</b>
            <div style="margin-bottom:8px;">Pick Categories</div>
            <div id="categoryTags" style="display:flex;gap:24px;flex-wrap:wrap;">
              <button type="button" class="tag-btn" data-value="Quick"><span style="font-size:1.2em;">⚡</span> Quick</button>
              <button type="button" class="tag-btn" data-value="Healthy"><span style="font-size:1.2em;">🥗</span> Healthy</button>
              <button type="button" class="tag-btn" data-value="Sweet"><span style="font-size:1.2em;">🍬</span> Sweet</button>
              <button type="button" class="tag-btn" data-value="Spicy"><span style="font-size:1.2em;">🌶️</span> Spicy</button>
              <button type="button" class="tag-btn" data-value="Comfort"><span style="font-size:1.2em;">💤</span> Comfort</button>
            </div>
            <input type="hidden" name="tags" id="tagsInput">

            <div style="margin-top:12px;">Pick Time of the Day</div>
            <div id="timeTags" style="display:flex;gap:18px;flex-wrap:wrap;margin-top:8px;">
              <button type="button" class="tag-btn time breakfast" data-value="Breakfast"><span class="dot breakfast"></span>Breakfast</button>
              <button type="button" class="tag-btn time lunch" data-value="Lunch"><span class="dot lunch"></span>Lunch</button>
              <button type="button" class="tag-btn time dinner" data-value="Dinner"><span class="dot dinner"></span>Dinner</button>
              <button type="button" class="tag-btn time snack" data-value="Snacks & Dessert"><span class="dot snack"></span>Snacks & Dessert</button>
              <button type="button" class="tag-btn time beverages" data-value="Beverages"><span class="dot beverages"></span>Beverages</button>
            </div>
            <input type="hidden" name="times" id="timesInput">
          </div>

          <div style="display:flex;gap:24px;margin-top:24px;">
            <button type="submit" name = "save-rec-btn"class="save-btn" value="1" style="flex:1;background:#fff;border:1.5px solid #222;border-radius:8px;padding:10px 0;font-size:1rem;font-weight:600;cursor:pointer;">SAVE RECIPE</button>
            <button type="button" onclick="cancelRecipe()" class="cancel-btn" style="flex:1;background:#fff;border:1.5px solid #bbb;border-radius:8px;padding:10px 0;font-size:1rem;font-weight:600;cursor:pointer;">CANCEL RECIPE</button>          </div>
            <input type="hidden" name="cancel_recipe" id="cancelRecipeInput" value="0">
          </div>
      </div>
    </div>
  </form>
`;

function setupTagButtons() {
  // Category tags
  const catBtns = document.querySelectorAll('#categoryTags .tag-btn');
  const tagsInput = document.getElementById('tagsInput');
  catBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      btn.classList.toggle('active');
      const selected = Array.from(catBtns)
        .filter(b => b.classList.contains('active'))
        .map(b => b.dataset.value);
      if (tagsInput) tagsInput.value = selected.join(',');
    });
  });

  // Time tags
  const timeBtns = document.querySelectorAll('#timeTags .tag-btn');
  const timesInput = document.getElementById('timesInput');
  timeBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      btn.classList.toggle('active');
      const selected = Array.from(timeBtns)
        .filter(b => b.classList.contains('active'))
        .map(b => b.dataset.value);
      if (timesInput) timesInput.value = selected.join(',');
    });
  });
}

function cancelRecipe() {
    document.getElementById('cancelRecipeInput').value = "1";
    document.getElementById('recipeForm').submit();
}

function attachViewRecipeHandlers() {
  document.querySelectorAll('.view-btn').forEach((btn, idx) => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const recipe = recipes[idx];
      document.getElementById('mainContent').innerHTML = renderRecipeDetail(recipe);

      // Attach back button event to reload page or show grid
      document.getElementById('backBtn').addEventListener('click', ev => {
        ev.preventDefault();
        window.location.reload();
      });

      // Attach heart toggle
      document.getElementById('detailHeart').addEventListener('click', function (e) {
        e.stopPropagation();
        const recipeId = this.getAttribute('data-recipe-id');
        const isFilled = this.classList.contains('filled');
        const setFav = isFilled ? 0 : 1;

        fetch('toggle_favorite.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/x-www-form-urlencoded'},
          body: `recipe_id=${recipeId}&set_fav=${setFav}`
        })
        .then(response => response.text())
        .then(data => {
          if (data.trim() === 'ok') {
            if (isFilled) {
              this.textContent = '♡';
              this.style.color = '';
              this.classList.remove('filled');
            } else {
              this.textContent = '♥';
              this.style.color = '#f43f5e';
              this.classList.add('filled');
            }
          } else {
            alert('Failed to update favorite!');
          }
        });
      });

      document.getElementById('editRecipeBtn').addEventListener('click', function() {
        document.getElementById('mainContent').innerHTML = recipeFormHTML;
        setupTagButtons();

        // Fill form fields
        document.querySelector('input[name="title"]').value = recipe.title;
        document.querySelector('input[name="servings"]').value = recipe.servings;
        document.querySelector('input[name="cook_hour"]').value = recipe.cook_time.split(':')[0];
        document.querySelector('input[name="cook_minute"]').value = recipe.cook_time.split(':')[1];
        document.querySelector('input[name="prep_hour"]').value = recipe.prep_time.split(':')[0];
        document.querySelector('input[name="prep_minute"]').value = recipe.prep_time.split(':')[1];

        // Ingredients
        const ingredientsList = document.getElementById('ingredientsList');
        ingredientsList.innerHTML = '';
        recipe.ingredients.forEach(ing => {
          const input = document.createElement('input');
          input.type = 'text';
          input.name = 'ingredients[]';
          input.className = 'input';
          input.style.width = '100%';
          input.style.marginBottom = '6px';
          input.value = ing;
          ingredientsList.appendChild(input);
        });

        // Instructions
        const instructionsList = document.getElementById('instructionsList');
        instructionsList.innerHTML = '';
        recipe.instructions.forEach((ins, idx) => {
          const div = document.createElement('div');
          div.style.display = 'flex';
          div.style.alignItems = 'center';
          div.style.marginBottom = '6px';

          const span = document.createElement('span');
          span.style.width = '24px';
          span.style.display = 'inline-block';
          span.textContent = idx + 1;

          const input = document.createElement('input');
          input.type = 'text';
          input.name = 'instructions[]';
          input.className = 'input';
          input.style.flex = '1';
          input.value = ins;

          div.appendChild(span);
          div.appendChild(input);
          instructionsList.appendChild(div);
        });

        // Tags (categories)
        const catBtns = document.querySelectorAll('#categoryTags .tag-btn');
        catBtns.forEach(btn => {
          if (recipe.categories.includes(btn.dataset.value)) {
            btn.classList.add('active');
          }
        });
        document.getElementById('tagsInput').value = recipe.categories.join(',');

        // Times (time of day)
        const timeBtns = document.querySelectorAll('#timeTags .tag-btn');
        timeBtns.forEach(btn => {
          if (recipe.timesOfDay.includes(btn.dataset.value)) {
            btn.classList.add('active');
          }
        });
        document.getElementById('timesInput').value = recipe.timesOfDay.join(',');

        if (recipe.image) {
          const label = document.getElementById('mainPhotoLabel');
          const placeholder = document.getElementById('mainPhotoPlaceholder');
          if (placeholder) placeholder.style.display = 'none';
          const oldImg = label.querySelector('img');
          if (oldImg) oldImg.remove();
          const img = document.createElement('img');
          img.src = recipe.image;
          img.style.width = '100%';
          img.style.height = '100%';
          img.style.objectFit = 'cover';
          img.style.position = 'absolute';
          img.style.top = 0;
          img.style.left = 0;
          img.style.borderRadius = '18px';
          label.appendChild(img);
        }

        const form = document.getElementById('recipeForm');
        const hiddenId = document.createElement('input');
        hiddenId.type = 'hidden';
        hiddenId.name = 'edit_recipe_id';
        hiddenId.value = recipe.id;
        form.appendChild(hiddenId);

        const addIngredientBtn = document.getElementById('addIngredient');
        addIngredientBtn.addEventListener('click', () => {
          const input = document.createElement('input');
          input.type = 'text';
          input.name = 'ingredients[]';
          input.className = 'input';
          input.style.width = '100%';
          input.style.marginBottom = '6px';
          document.getElementById('ingredientsList').appendChild(input);
        });

        const addInstructionBtn = document.getElementById('addInstruction');
        addInstructionBtn.addEventListener('click', () => {
          const instructionsList = document.getElementById('instructionsList');
          const count = instructionsList.children.length + 1;

          const div = document.createElement('div');
          div.style.display = 'flex';
          div.style.alignItems = 'center';
          div.style.marginBottom = '6px';

          const span = document.createElement('span');
          span.style.width = '24px';
          span.style.display = 'inline-block';
          span.textContent = count;

          const input = document.createElement('input');
          input.type = 'text';
          input.name = 'instructions[]';
          input.className = 'input';
          input.style.flex = '1';

          div.appendChild(span);
          div.appendChild(input);
          instructionsList.appendChild(div);
        });

        const mainPhotoInput = document.getElementById('mainPhoto');
        mainPhotoInput.addEventListener('change', function (e) {
          const file = e.target.files[0];
          if (!file) return;

          const reader = new FileReader();
          reader.onload = function (event) {
            const label = document.getElementById('mainPhotoLabel');
            const placeholder = document.getElementById('mainPhotoPlaceholder');

            if (placeholder) placeholder.style.display = 'none';

            const oldImg = label.querySelector('img');
            if (oldImg) oldImg.remove();

            const img = document.createElement('img');
            img.src = event.target.result;
            img.style.width = '100%';
            img.style.height = '100%';
            img.style.objectFit = 'cover';
            img.style.position = 'absolute';
            img.style.top = 0;
            img.style.left = 0;
            img.style.borderRadius = '18px';

            label.appendChild(img);
          };
          reader.readAsDataURL(file);
        });
      });

      document.getElementById('deleteRecipeBtn').addEventListener('click', function() {
        Swal.fire({
          title: 'Delete Recipe?',
          text: "This action cannot be undone.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!',
          cancelButtonText: 'Cancel',
          confirmButtonColor: '#d33',
          cancelButtonColor: '#bbb'
        }).then((result) => {
          if (result.isConfirmed) {
            fetch('dashboard.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/x-www-form-urlencoded'},
              body: `delete_recipe_id=${recipe.id}`
            })
            .then(res => res.text())
            .then(data => {
              if (data.trim() === 'ok') {
                Swal.fire('Deleted!', 'Your recipe has been deleted.', 'success').then(() => {
                  window.location.reload();
                });
              } else {
                Swal.fire('Error', 'Failed to delete recipe: ' + data, 'error');
              }
            });
          }
        });
      });
    });
  });
}

window.addEventListener('DOMContentLoaded', () => {
  attachViewRecipeHandlers();

  document.getElementById('createRecipeBtn').addEventListener('click', function (e) {
    e.preventDefault();
    document.getElementById('mainContent').innerHTML = recipeFormHTML;

    setupTagButtons();

    const addIngredientBtn = document.getElementById('addIngredient');
    addIngredientBtn.addEventListener('click', () => {
      const input = document.createElement('input');
      input.type = 'text';
      input.name = 'ingredients[]';
      input.className = 'input';
      input.style.width = '100%';
      input.style.marginBottom = '6px';
      document.getElementById('ingredientsList').appendChild(input);
    });

    const addInstructionBtn = document.getElementById('addInstruction');
    addInstructionBtn.addEventListener('click', () => {
      const instructionsList = document.getElementById('instructionsList');
      const count = instructionsList.children.length + 1;

      const div = document.createElement('div');
      div.style.display = 'flex';
      div.style.alignItems = 'center';
      div.style.marginBottom = '6px';

      const span = document.createElement('span');
      span.style.width = '24px';
      span.style.display = 'inline-block';
      span.textContent = count;

      const input = document.createElement('input');
      input.type = 'text';
      input.name = 'instructions[]';
      input.className = 'input';
      input.style.flex = '1';

      div.appendChild(span);
      div.appendChild(input);
      instructionsList.appendChild(div);
    });

    const mainPhotoInput = document.getElementById('mainPhoto');
    mainPhotoInput.addEventListener('change', function (e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (event) {
        const label = document.getElementById('mainPhotoLabel');
        const placeholder = document.getElementById('mainPhotoPlaceholder');

        if (placeholder) placeholder.style.display = 'none';

        const oldImg = label.querySelector('img');
        if (oldImg) oldImg.remove();

        const img = document.createElement('img');
        img.src = event.target.result;
        img.style.width = '100%';
        img.style.height = '100%';
        img.style.objectFit = 'cover';
        img.style.position = 'absolute';
        img.style.top = 0;
        img.style.left = 0;
        img.style.borderRadius = '18px';

        label.appendChild(img);
    };
    reader.readAsDataURL(file);
    });
  });
});

document.querySelector('.profile-btn').addEventListener('click', function(e) {
  e.preventDefault();
  document.getElementById('mainContent').innerHTML = profileHTML;

  const editBtn = document.getElementById('editPasswordBtn');
  const passInput = document.getElementById('profilePassword');
  let editing = false;

  editBtn.addEventListener('click', function() {
    if (!editing) {
      passInput.removeAttribute('readonly');
      passInput.type = 'text';
      passInput.value = '';
      passInput.placeholder = 'Enter new password';
      passInput.focus();
      editBtn.textContent = 'Save';
      editing = true;
    } else {
      const newPass = passInput.value.trim();
      if (newPass.length < 6) {
        Swal.fire('Error', 'Password must be at least 6 characters.', 'error');
        return;
      }
      fetch('dashboard.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `ajax_update_password=${encodeURIComponent(newPass)}`
      })
      .then(res => res.text())
      .then(data => {
        if (data.trim() === 'ok') {
          Swal.fire('Success', 'Password updated!', 'success');
          passInput.type = 'password';
          passInput.value = '********';
          passInput.setAttribute('readonly', true);
          editBtn.textContent = 'Edit Password';
          editing = false;
        } else {
          Swal.fire('Error', data, 'error');
        }
      });
    }
  });
});

document.querySelector('.logout-btn').addEventListener('click', function (e) {
    e.preventDefault();
    Swal.fire({
        title: 'Are you sure?',
        text: "You have to log back in if you log out!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, log out!',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',      
        color: '#222',                  
        customClass: {
            popup: 'my-popup',
            title: 'my-title',
            confirmButton: 'my-confirm-btn'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.querySelector('button[name="submit_logout"]').click();
            window.location.href = 'login.php';
        }
    });
}); 

const filter = "<?php echo $filter; ?>";

const profileHTML = `
  <div class="profile-card" style="max-width:500px;margin:40px auto;background:#fff;border-radius:18px;box-shadow:0 2px 16px rgba(0,0,0,0.06);padding:40px 32px;">
    <h2 style="margin-top:0;margin-bottom:24px;text-align:center;">👤 My Profile</h2>
    <div style="margin-bottom:18px;">
      <b>Email:</b><br>
      <span style="color:#444;"><?php echo htmlspecialchars($user_email); ?></span>
    </div>
    <div style="margin-bottom:18px;">
      <b>Password:</b><br>
      <input type="password" id="profilePassword" value="********" style="color:#444;width:70%;border:none;background:transparent;" readonly>
      <button type="button" id="editPasswordBtn" style="margin-left:8px;padding:4px 12px;border-radius:6px;border:1px solid #bbb;background:#f8f8f8;cursor:pointer;">Edit Password</button>
    </div>
    <div style="margin-bottom:18px;">
      <b>Total Recipes:</b><br>
      <span style="color:#444;"><?php echo count($user_recipes); ?></span>
    </div>
    <button type="button" onclick="window.location.reload()" style="margin-top:24px;background:#fff;border:1.5px solid #bbb;border-radius:8px;padding:10px 24px;font-size:1rem;font-weight:600;cursor:pointer;">Back to Dashboard</button>
  </div>
`;

const categoryEmojis = {
  'Quick': '⚡',
  'Healthy': '🥗',
  'Sweet': '🍬',
  'Spicy': '🌶️',
  'Comfort': '💤'
};

window.addEventListener('DOMContentLoaded', () => {
  const searchInput = document.querySelector('.search-bar');
  let ingredientMode = false; // false = keyword search, true = ingredient matching
  const ingredientBtn = document.querySelector('.ingredient-btn');

  function updateSearchUI() {
    if (ingredientMode) {
      searchInput.placeholder = "Enter ingredients, comma separated...";
      ingredientBtn.textContent = "🍲Ingredient Matching";
    } else {
      searchInput.placeholder = "Search by title or ingredient...";
      ingredientBtn.textContent = "🔤Keyword Search";
    }
    searchInput.value = "";
    searchInput.dispatchEvent(new Event('input'));
  }

  ingredientBtn.addEventListener('click', function () {
    ingredientMode = !ingredientMode;
    updateSearchUI();
  });

  searchInput.addEventListener('input', function () {
    const keyword = this.value.trim().toLowerCase();
    document.querySelectorAll('.recipe-card').forEach(card => {
      const title = card.querySelector('.recipe-title').textContent.toLowerCase();
      const recipeId = card.querySelector('.recipe-heart').getAttribute('data-recipe-id');
      const recipe = recipes.find(r => r.id == recipeId);
      const ingredients = recipe ? recipe.ingredients.map(i => i.toLowerCase()) : [];

      let show = true;
      if (!ingredientMode) {
        // Keyword Search: match title or any ingredient
        show = !keyword ||
          title.includes(keyword) ||
          ingredients.some(ing => ing.includes(keyword));
      } else {
        // Ingredient Matching: all entered ingredients must be present
        const inputIngredients = keyword.split(',').map(i => i.trim()).filter(i => i);
        show = inputIngredients.length === 0 || inputIngredients.every(ing =>
          ingredients.some(recipeIng => recipeIng.includes(ing))
        );
      }

      card.style.display = show ? '' : 'none';
    });
  });

  updateSearchUI();
});
</script>

<?php if (isset($recipesaved) && !$recipesaved && !empty($errors)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('mainContent').innerHTML = recipeFormHTML;
            setupTagButtons();

            // Fill form fields with previous input 
            <?php if (!empty($_POST)): ?>
                document.querySelector('input[name="title"]').value = <?php echo json_encode($_POST['title'] ?? ''); ?>;
                document.querySelector('input[name="servings"]').value = <?php echo json_encode($_POST['servings'] ?? ''); ?>;
                document.querySelector('input[name="cook_hour"]').value = <?php echo json_encode($_POST['cook_hour'] ?? ''); ?>;
                document.querySelector('input[name="cook_minute"]').value = <?php echo json_encode($_POST['cook_minute'] ?? ''); ?>;
                document.querySelector('input[name="prep_hour"]').value = <?php echo json_encode($_POST['prep_hour'] ?? ''); ?>;
                document.querySelector('input[name="prep_minute"]').value = <?php echo json_encode($_POST['prep_minute'] ?? ''); ?>;
                // Ingredients
                const ingredients = <?php echo json_encode($_POST['ingredients'] ?? []); ?>;
                const ingredientsList = document.getElementById('ingredientsList');
                ingredientsList.innerHTML = '';
                ingredients.forEach(function(ing) {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'ingredients[]';
                    input.className = 'input';
                    input.style.width = '100%';
                    input.style.marginBottom = '6px';
                    input.value = ing;
                    ingredientsList.appendChild(input);
                });
                // Instructions
                const instructions = <?php echo json_encode($_POST['instructions'] ?? []); ?>;
                const instructionsList = document.getElementById('instructionsList');
                instructionsList.innerHTML = '';
                instructions.forEach(function(ins, idx) {
                    const div = document.createElement('div');
                    div.style.display = 'flex';
                    div.style.alignItems = 'center';
                    div.style.marginBottom = '6px';
                    const span = document.createElement('span');
                    span.style.width = '24px';
                    span.style.display = 'inline-block';
                    span.textContent = idx + 1;
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.name = 'instructions[]';
                    input.className = 'input';
                    input.style.flex = '1';
                    input.value = ins;
                    div.appendChild(span);
                    div.appendChild(input);
                    instructionsList.appendChild(div);
                });
                // Tags
                const tags = <?php echo json_encode(explode(',', $_POST['tags'] ?? '')); ?>;
                document.querySelectorAll('#categoryTags .tag-btn').forEach(btn => {
                    if (tags.includes(btn.dataset.value)) btn.classList.add('active');
                });
                document.getElementById('tagsInput').value = tags.join(',');
                // Times
                const times = <?php echo json_encode(explode(',', $_POST['times'] ?? '')); ?>;
                document.querySelectorAll('#timeTags .tag-btn').forEach(btn => {
                    if (times.includes(btn.dataset.value)) btn.classList.add('active');
                });
                document.getElementById('timesInput').value = times.join(',');
            <?php endif; ?>
        });
    </script>
<?php endif; ?>
</body>
</html>
