--
-- Table structure for table 'tblAVSAP_AVSAPAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPAssessments') DROP TABLE tblAVSAP_AVSAPAssessments;
CREATE TABLE tblAVSAP_AVSAPAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  RepositoryID INT NOT NULL default '0',
  SubAssessmentType INT NOT NULL default '0',
  Name varchar(100) NOT NULL default '',
  CollectionID INT NOT NULL default '0',
  CollectionContentID INT NOT NULL default '0',
  StorageFacilityID INT NOT NULL default '0',
  Format INT NOT NULL default '0',
  BaseComposition INT NOT NULL default '0',
  UniqueMaterial decimal(3,2) NOT NULL default '0.00',
  OriginalMaterial decimal(3,2) NOT NULL default '0.00',
  IsPlayed decimal(3,2) NOT NULL default '0.00',
  HasPlaybackEquip decimal(3,2) NOT NULL default '0.00',
  RecentlyPlayedBack decimal(3,2) NOT NULL default '0.00',
  HasConditionInfo decimal(3,2) NOT NULL default '0.00',
  OrientedCorrectly decimal(3,2) NOT NULL default '0.00',
  AppropriateContainer decimal(3,2) NOT NULL default '0.00',
  Labeling decimal(3,2) NOT NULL default '0.00',
  PhysicalDamage decimal(3,2) NOT NULL default '0.00',
  MoldLevel decimal(3,2) NOT NULL default '0.00',
  PestDamage decimal(3,2) NOT NULL default '0.00',
  Significance decimal(3,2) NOT NULL default '0.00',
  Score decimal(9,2) NOT NULL default '0.00',
  Notes TEXT NULL default NULL
);



--
-- Table structure for table 'tblAVSAP_AVSAPAudioCassetteAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPAudioCassetteAssessments') DROP TABLE tblAVSAP_AVSAPAudioCassetteAssessments;
CREATE TABLE tblAVSAP_AVSAPAudioCassetteAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  RecordProtection decimal(3,2) NOT NULL default '0.00',
  CartridgeCondition decimal(3,2) NOT NULL default '0.00',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00',
  CassetteLength decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPFilmAssessments'
--

IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPFilmAssessments') DROP TABLE tblAVSAP_AVSAPFilmAssessments;
CREATE TABLE tblAVSAP_AVSAPFilmAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  OnCore decimal(3,2) NOT NULL default '0.00',
  InColor decimal(3,2) NOT NULL default '0.00',
  SoundtrackType decimal(3,2) NOT NULL default '0.00',
  HasLeader decimal(3,2) NOT NULL default '0.00',
  FilmBase decimal(3,2) NOT NULL default '0.00',
  FilmDecay decimal(3,2) NOT NULL default '0.00',
  FilmType decimal(3,2) NOT NULL default '0.00',
  Shrinkage decimal(3,2) NOT NULL default '0.00',
  SpliceIntegrity decimal(3,2) NOT NULL default '0.00',
  MagStockBreakdown decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPGroovedCylinderAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPGroovedCylinderAssessments') DROP TABLE tblAVSAP_AVSAPGroovedCylinderAssessments;
CREATE TABLE tblAVSAP_AVSAPGroovedCylinderAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  CylComposition decimal(3,2) NOT NULL default '0.00',
  DustLevel decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPGroovedDiscAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPGroovedDiscAssessments') DROP TABLE tblAVSAP_AVSAPGroovedDiscAssessments;
CREATE TABLE tblAVSAP_AVSAPGroovedDiscAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  DiscComposition decimal(3,2) NOT NULL default '0.00',
  HasInnerSleeve decimal(3,2) NOT NULL default '0.00',
  CoreMaterial decimal(3,2) NOT NULL default '0.00',
  AcidDeposits decimal(3,2) NOT NULL default '0.00',
  RecordingSpeed decimal(3,2) NOT NULL default '0.00',
  DustLevel decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPInstitutions'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPInstitutions') DROP TABLE tblAVSAP_AVSAPInstitutions;
CREATE TABLE tblAVSAP_AVSAPInstitutions (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  RepositoryID INT NOT NULL default '0',
  Name varchar(100) NOT NULL,
  PreservationPlan decimal(3,2) NOT NULL default '0.00',
  AVPreservationPlan decimal(3,2) NOT NULL default '0.00',
  CollectionPolicy decimal(3,2) NOT NULL default '0.00',
  AccessCopies decimal(3,2) NOT NULL default '0.00',
  DigitalCopies decimal(3,2) NOT NULL default '0.00',
  OwnershipRecords decimal(3,2) NOT NULL default '0.00',
  AllowPlayBack decimal(3,2) NOT NULL default '0.00',
  AllowLoaningInstitutions decimal(3,2) NOT NULL default '0.00',
  AllowLoaningOther decimal(3,2) NOT NULL default '0.00',
  StaffCleanRepair decimal(3,2) NOT NULL default '0.00',
  StaffVisualInspections decimal(3,2) NOT NULL default '0.00',
  StaffPlayBackInspections decimal(3,2) NOT NULL default '0.00',
  DedicatedInspectionSpace decimal(3,2) NOT NULL default '0.00',
  MaintainPlaybackEquipment decimal(3,2) NOT NULL default '0.00',
  EquipmentManuals decimal(3,2) NOT NULL default '0.00',
  EquipmentPartsService decimal(3,2) NOT NULL default '0.00',
  CatalogCollections decimal(3,2) NOT NULL default '0.00',
  DisasterRecoveryPlan decimal(3,2) NOT NULL default '0.00',
  AVDisasterRecoveryPlan decimal(3,2) NOT NULL default '0.00',
  AccessAVDisasterRecoveryTools decimal(3,2) NOT NULL default '0.00',
  Score decimal(9,2) default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPOpenReelAudioAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPOpenReelAudioAssessments') DROP TABLE tblAVSAP_AVSAPOpenReelAudioAssessments;
CREATE TABLE tblAVSAP_AVSAPOpenReelAudioAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  HasLeader decimal(3,2) NOT NULL default '0.00',
  TapeBase decimal(3,2) NOT NULL default '0.00',
  TapeDecay decimal(3,2) NOT NULL default '0.00',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00',
  SpliceIntegrity decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPOpenReelVideoAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPOpenReelVideoAssessments') DROP TABLE tblAVSAP_AVSAPOpenReelVideoAssessments;
CREATE TABLE tblAVSAP_AVSAPOpenReelVideoAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPOpticalMediaAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPOpticalMediaAssessments') DROP TABLE tblAVSAP_AVSAPOpticalMediaAssessments;
CREATE TABLE tblAVSAP_AVSAPOpticalMediaAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  LaserRot decimal(3,2) NOT NULL default '0.00',
  DyeLayerProblems decimal(3,2) NOT NULL default '0.00',
  PerformedChecksum decimal(3,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPStorageFacilities'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPStorageFacilities') DROP TABLE tblAVSAP_AVSAPStorageFacilities;
CREATE TABLE tblAVSAP_AVSAPStorageFacilities (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  RepositoryID INT NOT NULL default '0',
  LocationID INT NOT NULL default '0',
  Name varchar(100) NOT NULL,
  AvgTemp decimal(3,2) NOT NULL default '0.00',
  TempVariance decimal(3,2) NOT NULL default '0.00',
  AvgHumidity decimal(3,2) NOT NULL default '0.00',
  HumidityVariance decimal(3,2) NOT NULL default '0.00',
  HasFireDetection decimal(3,2) NOT NULL default '0.00',
  HasFireSuppression decimal(3,2) NOT NULL default '0.00',
  HasWaterDetection decimal(3,2) NOT NULL default '0.00',
  MaterialsOnFloor decimal(3,2) NOT NULL default '0.00',
  SecurityLevel decimal(3,2) NOT NULL default '0.00',
  Score decimal(9,2) NOT NULL default '0.00'
);



--
-- Table structure for table 'tblAVSAP_AVSAPVideoCassetteAssessments'
--
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPVideoCassetteAssessments') DROP TABLE tblAVSAP_AVSAPVideoCassetteAssessments;
CREATE TABLE tblAVSAP_AVSAPVideoCassetteAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  RecordProtection decimal(3,2) NOT NULL default '0.00',
  CartridgeCondition decimal(3,2) NOT NULL default '0.00',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00'
);


-- Table structure for table 'tblAVSAP_AVSAPWireAudioAssessments'
IF EXISTS(SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'tblAVSAP_AVSAPWireAudioAssessments') DROP TABLE tblAVSAP_AVSAPWireAudioAssessments;
CREATE TABLE tblAVSAP_AVSAPWireAudioAssessments (
  ID INT NOT NULL IDENTITY(1,1) PRIMARY KEY,
  AssessmentID INT NOT NULL default '0' UNIQUE,
  RustLevel decimal(3,2) NOT NULL default '0.00'
);


-- Insert default data for table tblCore_Modules

DECLARE @package_avsap INT; SET @package_avsap = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_avsap,'avsapinstitutions');
DECLARE @package_avsap INT; SET @package_avsap = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_avsap,'avsapstoragefacilities');
DECLARE @package_avsap INT; SET @package_avsap = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_avsap,'avsapassessments');
DECLARE @package_avsap INT; SET @package_avsap = (SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'); INSERT INTO tblCore_Modules (PackageID,Script) VALUES (@package_avsap,'avsapassessmentreport');
