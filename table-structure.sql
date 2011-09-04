SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `tumblrpicsdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `twitter-pics-to-tumblr`
--

CREATE TABLE IF NOT EXISTS `twitter-pics-to-tumblr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shorturl` varchar(50) NOT NULL,
  `source` varchar(500) NOT NULL,
  `bitlyurl` varchar(500) NOT NULL,
  `title` varchar(500) NOT NULL,
  `photourl` varchar(500) NOT NULL,
  `authname` varchar(500) NOT NULL,
  `authurl` varchar(500) NOT NULL,
  `authimg` varchar(500) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shorturl` (`shorturl`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
