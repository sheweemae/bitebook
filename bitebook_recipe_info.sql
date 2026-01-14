-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 24, 2025 at 03:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bitebook_recipe_info`
--

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `ingredient_id` int(11) NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `ingredient_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`ingredient_id`, `recipe_id`, `ingredient_name`) VALUES
(298, 3, '2 lbs cassava grated'),
(299, 3, '2 cups coconut milk'),
(300, 3, '6 oz evaporated milk'),
(301, 3, '3 pieces egg'),
(302, 3, '1/4 cup butter melted'),
(303, 3, '6 tablespoons cheddar cheese grated'),
(304, 3, '1/2 cup condensed milk'),
(305, 3, '14 tablespoons granulated white sugar'),
(306, 3, '2 tablespoons flour'),
(307, 3, '2 tablespoons sugar'),
(308, 3, '1/2 cup condensed milk'),
(309, 3, '2 tablespoons cheddar cheese grated'),
(310, 3, '2 cups coconut milk'),
(357, 2, '1 1/2 lb ground pork'),
(358, 2, '1 1/2 cups potatoes diced'),
(359, 2, '1 cup carrots diced'),
(360, 2, '8 ounces tomato sauce'),
(361, 2, '6 cloves garlic crushed'),
(362, 2, '1 medium-sized onion minced'),
(363, 2, '1 teaspoon granulated sugar'),
(364, 2, '1 piece beef or pork cube'),
(365, 2, '4 boiled eggs shelled (optional)'),
(366, 2, 'Salt and pepper to taste'),
(367, 2, '3 tablespoons cooking oil'),
(368, 2, '1 cup water'),
(426, 6, '1-2 Tbsp. syrup-packed red mung beans, white beans, and/or garbanzo beans'),
(427, 6, '1 Tbsp. sweetened jackfruit'),
(428, 6, '1 Tbsp. macapuno strings (coconut sport)'),
(429, 6, '1 Tbsp. nata de coconut (coconut gel)'),
(430, 6, '1 cup shaved ice'),
(431, 6, '1 Tbsp. ube halaya jam'),
(432, 6, '2 Tbsp. leche flan, purchased'),
(433, 6, '2 to 4 Tbsp. evaporated milk'),
(434, 6, '2 Tbsp. ube ice cream'),
(459, 1, '2 lbs chicken'),
(460, 1, '3 pieces dried bay leaves'),
(461, 1, '4 tablespoons soy sauce'),
(462, 1, '6 tablespoons white vinegar'),
(463, 1, '5 cloves garlic'),
(464, 1, '1 1/2 cups water'),
(465, 1, '3 tablespoons cooking oil'),
(466, 1, '1 teaspoon sugar'),
(467, 1, '1/4 teaspoon salt '),
(468, 1, '1 teaspoon whole peppercorn'),
(469, 8, '1 cup (120g) all-purpose flour'),
(470, 8, '1/2 teaspoon baking powder'),
(471, 8, '1/2 teaspoon salt'),
(472, 8, '1/2 cup (43g) unsweetened cocoa powder'),
(473, 8, '1 teaspoon espresso powder optional (don\'t use if you don\'t like coffee)'),
(474, 8, '3/4 cup (170g) unsalted butter'),
(475, 8, '2 Tablespoons (28ml) oil canola, vegetable, or coconut will work'),
(476, 8, '1 and 1/3 cups (265g) granulated sugar divided'),
(477, 8, '2 large large eggs'),
(478, 8, '1 large egg yolk'),
(479, 8, '2 teaspoons vanilla extract optional, but recommended'),
(480, 8, '3/4 cup (128 grams) chocolate chips'),
(495, 7, '3 lbs oxtail cut in 2 inch slices you an also use tripe or beef slices'),
(496, 7, '1 piece small banana flower bud sliced'),
(497, 7, '1 bundle pechay or bok choy'),
(498, 7, '1 bundle string beans cut into 2 inch slices'),
(499, 7, '4 pieces eggplants sliced'),
(500, 7, '1 cup ground peanuts'),
(501, 7, '1/2 cup peanut butter'),
(502, 7, '1/2 cup shrimp paste'),
(503, 7, '34 Ounces water about 1 Liter'),
(504, 7, '1/2 cup annatto seeds soaked in a cup of water'),
(505, 7, '1/2 cup toasted ground rice'),
(506, 7, '1 tbsp garlic minced'),
(507, 7, '1 piece onion chopped'),
(508, 7, 'salt and pepper');

-- --------------------------------------------------------

--
-- Table structure for table `instructions`
--

CREATE TABLE `instructions` (
  `instruction_id` int(11) NOT NULL,
  `recipe_id` int(11) DEFAULT NULL,
  `step_number` int(11) NOT NULL,
  `instruction_text` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructions`
--

INSERT INTO `instructions` (`instruction_id`, `recipe_id`, `step_number`, `instruction_text`) VALUES
(242, 3, 1, 'Make the batter by combining the grated cassava, butter, 1/2 cup condensed milk, 6 oz. evaporated milk, 6 tablespoons cheddar cheese, 14 tablespoons sugar, and 2 eggs in a mixing bowl and mix thoroughly.'),
(243, 3, 2, 'Add the 2 cups coconut milk in the mixing bowl where the mixed ingredients are. Mix again.'),
(244, 3, 3, 'Grease the baking tray then pour-in the batter (these are the ingredients that you just mixed together).'),
(245, 3, 4, 'Pre -heat oven for 350 degrees Fahrenheit for 10 minutes then put-in the baking tray with batter and bake for 1 hour.Remove from the oven and set aside.'),
(246, 3, 5, 'Meanwhile prepare the topping by combining 2 tablespoons sugar and flour in the heated saucepan.'),
(247, 3, 6, 'Pour-in 1/2 cup condensed milk then mix thoroughly.'),
(248, 3, 7, 'Add 2 tablespoons cheddar cheese while stirring constantly.'),
(249, 3, 8, 'Pour 2 cups of coconut milk and stir constantly for 10 minutes'),
(250, 3, 9, 'Pour the topping over the Cassava Cake (baked batter) and spread evenly.'),
(251, 3, 10, 'Separate the yolk from the egg white of the remaining egg (we’ll be needing the egg white only)'),
(252, 3, 11, 'Glaze the topping with the egg white using a basting brush. Simply dip the brush to the egg white and brush it on the cassava cake.'),
(253, 3, 12, 'Set your oven to broil mode. Broil the Cassava cake until color turns light brown.'),
(254, 3, 13, 'Garnish with extra grated cheese on top. Serve. Share and enjoy!'),
(290, 2, 1, 'Heat a cooking pot and pour-in the cooking oil.'),
(291, 2, 2, 'When the oil is hot enough, put-in the garlic and sauté until the color turns light brown.'),
(292, 2, 3, 'Add the onions and sauté until the texture becomes soft.'),
(293, 2, 4, 'Put-in the ground pork and cook for 5 minutes.'),
(294, 2, 5, 'Add the beef or pork cube, tomato sauce, and water and let boil. Simmer for 20 minutes.'),
(295, 2, 6, 'Put the carrots and potatoes in then stir until every ingredient is properly distributed. Simmer for 10 to 12 minutes.'),
(296, 2, 7, 'Add salt, ground black pepper, and sugar then stir.'),
(297, 2, 8, 'Put in the boiled eggs and turn off the heat.'),
(298, 2, 9, 'Transfer to a serving bowl and serve.'),
(299, 2, 10, 'Share and enjoy!'),
(330, 6, 1, 'In a tall glass or a trifle dish, layer the assorted beans, sweetened jackfruit, coconut gel, and coconut strings. Feel free to add more or less of any of the ingredients you enjoy.'),
(331, 6, 2, 'Add shaved ice to fill the glass, leaving room for toppings. Top with ube halaya, leche flan, and ube ice cream.'),
(332, 6, 3, 'To finish the halo-halo recipe, drizzle evaporated milk over the top. Mix and enjoy. Halo-halo is best enjoyed right away.'),
(348, 1, 1, 'Combine chicken, soy sauce, and garlic in a large bowl. Mix well. Marinate the chicken for at least 1 hour. Note: the longer the time, the better'),
(349, 1, 2, 'Heat a cooking pot. Pour cooking oil.'),
(350, 1, 3, 'When the oil is hot enough, pan-fry the marinated chicken for 2 minutes per side.'),
(351, 1, 4, 'Pour-in the remaining marinade, including garlic. Add water. Bring to a boil'),
(352, 1, 5, 'Add dried bay leaves and whole peppercorn. Simmer for 30 minutes or until the chicken gets tender'),
(353, 1, 6, 'Add vinegar. Stir and cook for 10 minutes.'),
(354, 1, 7, 'Put-in the sugar, and salt. Stir and turn the heat off.Serve hot. Share and Enjoy!'),
(355, 8, 1, 'Preheat oven to 350 degrees (F) (175 degreed C). Line an 9×9-inch baking pan with parchment paper. Spray lightly with non-stick baking spray and set aside.'),
(356, 8, 2, 'In a large bowl, sift together the flour, baking powder, salt, cocoa powder, and espresso powder. Set aside until needed. '),
(357, 8, 3, 'In a medium saucepan, combine the butter, oil, and 1/3 cup of the sugar. Heat over medium heat, stirring frequently, until butter is completely melted. Remove from heat. '),
(358, 8, 4, 'In a large mixing bowl, combine the eggs, egg yolk, vanilla (if using) and remaining sugar. Whisk until well combined, about 30 seconds. '),
(359, 8, 5, 'Slowly, pour the warm butter mixture into the egg mixture, adding it very gradually (a little bit at a time) and whisking constantly until completely combined.'),
(360, 8, 6, 'Add in the dry ingredients and chocolate chips and, using a rubber spatula, slowly stir until just combined. *Do not over mix! Stop stirring when you see the last trace of dry ingredients. Over mixing will give you cakey brownies.'),
(361, 8, 7, 'Scrape the batter into the prepared pan and smooth the top. '),
(362, 8, 8, 'Bake for 28 (to 30 minutes) or until the edges are firm and the top is shiny and slightly cracked. '),
(363, 8, 9, 'Place pan on a cooling rack and cool completely before slicing. '),
(372, 7, 1, 'In a large pot, bring the water to a boil'),
(373, 7, 2, 'Put in the oxtail followed by the onions and simmer for 2.5 to 3 hrs or until tender (35 minutes if using a pressure cooker)'),
(374, 7, 3, 'Once the meat is tender, add the ground peanuts, peanut butter, and coloring (water from the annatto seed mixture) and simmer for 5 to 7 minutes'),
(375, 7, 4, 'Add the toasted ground rice and simmer for 5 minutes'),
(376, 7, 5, 'On a separate pan, saute the garlic then add the banana flower, eggplant, and string beans and cook for 5 minutes'),
(377, 7, 6, 'Transfer the cooked vegetables to the large pot (where the rest of the ingredients are)'),
(378, 7, 7, 'Add salt and pepper to taste'),
(379, 7, 8, 'Serve hot with shrimp paste. Enjoy!');

-- --------------------------------------------------------

--
-- Table structure for table `recipe_info`
--

CREATE TABLE `recipe_info` (
  `recipe_id` int(11) NOT NULL,
  `cooking_time` time DEFAULT NULL,
  `prep_time` time DEFAULT NULL,
  `servings` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recipe_info`
--

INSERT INTO `recipe_info` (`recipe_id`, `cooking_time`, `prep_time`, `servings`) VALUES
(1, '00:35:00', '00:05:00', 8),
(2, '00:40:00', '00:10:00', 6),
(3, '01:00:00', '00:15:00', 10),
(6, '00:00:00', '00:10:00', 1),
(7, '02:30:00', '00:10:00', 6),
(8, '00:28:00', '00:10:00', 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`ingredient_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `instructions`
--
ALTER TABLE `instructions`
  ADD PRIMARY KEY (`instruction_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `recipe_info`
--
ALTER TABLE `recipe_info`
  ADD PRIMARY KEY (`recipe_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `ingredient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=513;

--
-- AUTO_INCREMENT for table `instructions`
--
ALTER TABLE `instructions`
  MODIFY `instruction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=384;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `bitebook_recipe_list`.`recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `instructions`
--
ALTER TABLE `instructions`
  ADD CONSTRAINT `instructions_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `bitebook_recipe_list`.`recipes` (`recipe_id`) ON DELETE CASCADE;

--
-- Constraints for table `recipe_info`
--
ALTER TABLE `recipe_info`
  ADD CONSTRAINT `recipe_info_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `bitebook_recipe_list`.`recipes` (`recipe_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
