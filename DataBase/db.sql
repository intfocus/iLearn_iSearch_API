DROP TABLE `categories`;

CREATE TABLE `categories` (
  `CategoryId` int(11) NOT NULL AUTO_INCREMENT,
  `CategoryName` varchar(100) NOT NULL,
  `ParentId` int(11) NOT NULL,
  `DeptList` varchar(255) DEFAULT 'All',
  `FilePath` varchar(255) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `PAList` varchar(255) DEFAULT 'All',
  `ProductList` varchar(255) DEFAULT 'All',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`CategoryId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `depts`;

CREATE TABLE `depts` (
  `DeptId` int(11) NOT NULL AUTO_INCREMENT,
  `DeptName` varchar(100) NOT NULL,
  `DeptCode` varchar(20) DEFAULT NULL,
  `ParentId` int(11) NOT NULL DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '0',
  `PAList` varchar(255) NOT NULL DEFAULT 'All',
  `ProductList` varchar(255) NOT NULL DEFAULT 'All',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`DeptId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `files`;

CREATE TABLE `files` (
  `FileId` int(11) NOT NULL AUTO_INCREMENT,
  `FileName` varchar(255) NOT NULL,
  `FileTitle` varchar(255) DEFAULT NULL,
  `FileDesc` varchar(255) DEFAULT NULL,
  `FilePath` varchar(255) NOT NULL,
  `SmallGifPath` varchar(255) NOT NULL,
  `PageNo` int(11) NOT NULL,
  `FileType` int(11) NOT NULL,
  `Status` int(11) NOT NULL,
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `CategoryId` int(11) DEFAULT NULL,
  `ZipSize` int(11) DEFAULT NULL,
  PRIMARY KEY (`FileId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `functions`;

CREATE TABLE `functions` (
  `FunctionId` int(11) NOT NULL AUTO_INCREMENT,
  `FunctionName` varchar(50) NOT NULL,
  `FunctionType` int(11) NOT NULL DEFAULT '0',
  `CreatedTime` datetime DEFAULT NULL,
  `Icon` varchar(100) DEFAULT NULL,
  `CodePath` varchar(200) DEFAULT NULL,
  `Rank` int(11) DEFAULT NULL,
  PRIMARY KEY (`FunctionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `news`;

CREATE TABLE `news` (
  `NewId` int(11) NOT NULL AUTO_INCREMENT,
  `NewTitle` varchar(255) NOT NULL,
  `NewMsg` varchar(255) NOT NULL,
  `DeptList` varchar(255) NOT NULL DEFAULT 'All',
  `Status` int(11) NOT NULL DEFAULT '0',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `OccurTime` datetime DEFAULT NULL,
  PRIMARY KEY (`NewId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `privileges`;

CREATE TABLE `privileges` (
  `PrivilegeId` int(11) NOT NULL AUTO_INCREMENT,
  `UserId` int(11) NOT NULL,
  `FunctionId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '0',
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  PRIMARY KEY (`PrivilegeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE `users`;

CREATE TABLE `users` (
  `UserId` int(11) NOT NULL AUTO_INCREMENT,
  `UserName` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `DeptId` int(11) DEFAULT '0',
  `Status` int(11) NOT NULL DEFAULT '1',
  `CanApprove` int(11) NOT NULL DEFAULT '0',
  `JobGrade` int(11) DEFAULT NULL,
  `CreatedUser` int(11) DEFAULT NULL,
  `CreatedTime` datetime DEFAULT NULL,
  `EditUser` int(11) DEFAULT NULL,
  `EditTime` datetime DEFAULT NULL,
  `EmployeeId` varchar(20) NOT NULL,
  PRIMARY KEY (`UserId`),
  UNIQUE KEY `EmployeeId` (`EmployeeId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


insert categories(CategoryName,ParentId,DeptList,FilePath,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('总分类',0,'All','d:\\tmp\\file\\',1,'All','All',1,now(),1,now());


insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('卢安邦','PO',0,1,'ALL','ALL',1,now(),1,now());

insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('王卫','VCO',1,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('MSFE','MSFE',1,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('TRAINING','TRAINING',1,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('OTHERS','OTHERS',1,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('MED','MED',1,1,'ALL','ALL',1,now(),1,now());

insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('曲耀斌','KA Head',2,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('CBU Head','CBU Head',2,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('GBU Head','GBU Head',2,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('GBU NSD','GBU NSD',2,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('张晓东','OBU Head',2,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('张伟','RBU Head',2,1,'ALL','ALL',1,now(),1,now());

insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('KA','KA',7,1,'ALL','ALL',1,now(),1,now());

insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('CBU-MKT','CBU-MKT',8,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('OBU-MKT','OBU-MKT',8,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('左明星','CBU NSD',8,1,'ALL','ALL',1,now(),1,now());
insert depts(DeptName,DeptCode,ParentId,Status,PAList,ProductList,CreatedUser,CreatedTime,EditUser,EditTime) values('吴文凯','OBU-NSD',8,1,'ALL','ALL',1,now(),1,now());
