--
-- Table structure for table 'tblAVSAP_AVSAPAssessments'
--
DROP TABLE IF EXISTS tblAVSAP_AVSAPAssessments;
CREATE TABLE tblAVSAP_AVSAPAssessments (
  ID int(11) NOT NULL auto_increment,
  RepositoryID int(11) NOT NULL default '0',
  SubAssessmentType int(11) NOT NULL default '0',
  `Name` varchar(100) NOT NULL default '',
  CollectionID int(11) NOT NULL default '0',
  CollectionContentID int(11) NOT NULL default '0',
  StorageFacilityID int(11) NOT NULL default '0',
  Format int(11) NOT NULL default '0',
  BaseComposition int(11) NOT NULL default '0',
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
  Notes TEXT default NULL,
  PRIMARY KEY  (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPAudioCassetteAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPAudioCassetteAssessments;
CREATE TABLE tblAVSAP_AVSAPAudioCassetteAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  RecordProtection decimal(3,2) NOT NULL default '0.00',
  CartridgeCondition decimal(3,2) NOT NULL default '0.00',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00',
  CassetteLength decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPFilmAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPFilmAssessments;
CREATE TABLE tblAVSAP_AVSAPFilmAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  OnCore decimal(3,2) NOT NULL default '0.00',
  InColor decimal(3,2) NOT NULL default '0.00',
  SoundtrackType decimal(3,2) NOT NULL default '0.00',
  HasLeader decimal(3,2) NOT NULL default '0.00',
  FilmBase decimal(3,2) NOT NULL default '0.00',
  FilmDecay decimal(3,2) NOT NULL default '0.00',
  FilmType decimal(3,2) NOT NULL default '0.00',
  Shrinkage decimal(3,2) NOT NULL default '0.00',
  SpliceIntegrity decimal(3,2) NOT NULL default '0.00',
  MagStockBreakdown decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPGroovedCylinderAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPGroovedCylinderAssessments;
CREATE TABLE tblAVSAP_AVSAPGroovedCylinderAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  CylComposition decimal(3,2) NOT NULL default '0.00',
  DustLevel decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPGroovedDiscAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPGroovedDiscAssessments;
CREATE TABLE tblAVSAP_AVSAPGroovedDiscAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  DiscComposition decimal(3,2) NOT NULL default '0.00',
  HasInnerSleeve decimal(3,2) NOT NULL default '0.00',
  CoreMaterial decimal(3,2) NOT NULL default '0.00',
  AcidDeposits decimal(3,2) NOT NULL default '0.00',
  RecordingSpeed decimal(3,2) NOT NULL default '0.00',
  DustLevel decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPInstitutions'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPInstitutions;
CREATE TABLE tblAVSAP_AVSAPInstitutions (
  ID int(11) NOT NULL auto_increment,
  RepositoryID int(11) NOT NULL default '0',
  `Name` varchar(100) NOT NULL,
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
  Score decimal(9,2) default '0.00',
  PRIMARY KEY  (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPOpenReelAudioAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPOpenReelAudioAssessments;
CREATE TABLE tblAVSAP_AVSAPOpenReelAudioAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  HasLeader decimal(3,2) NOT NULL default '0.00',
  TapeBase decimal(3,2) NOT NULL default '0.00',
  TapeDecay decimal(3,2) NOT NULL default '0.00',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00',
  SpliceIntegrity decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPOpenReelVideoAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPOpenReelVideoAssessments;
CREATE TABLE tblAVSAP_AVSAPOpenReelVideoAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPOpticalMediaAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPOpticalMediaAssessments;
CREATE TABLE tblAVSAP_AVSAPOpticalMediaAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  LaserRot decimal(3,2) NOT NULL default '0.00',
  DyeLayerProblems decimal(3,2) NOT NULL default '0.00',
  PerformedChecksum decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPStorageFacilities'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPStorageFacilities;
CREATE TABLE tblAVSAP_AVSAPStorageFacilities (
  ID int(11) NOT NULL auto_increment,
  RepositoryID int(11) NOT NULL default '0',
  LocationID int(11) NOT NULL default '0',
  `Name` varchar(100) NOT NULL,
  AvgTemp decimal(3,2) NOT NULL default '0.00',
  TempVariance decimal(3,2) NOT NULL default '0.00',
  AvgHumidity decimal(3,2) NOT NULL default '0.00',
  HumidityVariance decimal(3,2) NOT NULL default '0.00',
  HasFireDetection decimal(3,2) NOT NULL default '0.00',
  HasFireSuppression decimal(3,2) NOT NULL default '0.00',
  HasWaterDetection decimal(3,2) NOT NULL default '0.00',
  MaterialsOnFloor decimal(3,2) NOT NULL default '0.00',
  SecurityLevel decimal(3,2) NOT NULL default '0.00',
  Score decimal(9,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



--
-- Table structure for table 'tblAVSAP_AVSAPVideoCassetteAssessments'
--

DROP TABLE IF EXISTS tblAVSAP_AVSAPVideoCassetteAssessments;
CREATE TABLE tblAVSAP_AVSAPVideoCassetteAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  RecordProtection decimal(3,2) NOT NULL default '0.00',
  CartridgeCondition decimal(3,2) NOT NULL default '0.00',
  StickyShed decimal(3,2) NOT NULL default '0.00',
  WindQuality decimal(3,2) NOT NULL default '0.00',
  PlaybackSqueal decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Table structure for table 'tblAVSAP_AVSAPWireAudioAssessments'

DROP TABLE IF EXISTS tblAVSAP_AVSAPWireAudioAssessments;
CREATE TABLE tblAVSAP_AVSAPWireAudioAssessments (
  ID int(11) NOT NULL auto_increment,
  AssessmentID int(11) NOT NULL default '0',
  RustLevel decimal(3,2) NOT NULL default '0.00',
  PRIMARY KEY  (ID),
  UNIQUE KEY AssessmentID (AssessmentID)
) DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- Insert default data for table tblCore_Modules

INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'),'avsapinstitutions');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'),'avsapstoragefacilities');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'),'avsapassessments');
INSERT INTO tblCore_Modules (PackageID,Script) VALUES ((SELECT ID FROM tblCore_Packages WHERE APRCode = 'avsap'),'avsapassessmentreport');
