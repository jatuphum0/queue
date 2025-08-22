CREATE TABLE `queue_called` (
  `id` int AUTO_INCREMENT PRIMARY KEY,
  `vn` varchar(13) CHARACTER SET tis620 COLLATE tis620_thai_ci NULL DEFAULT NULL,
  `hn` varchar(9) CHARACTER SET tis620 COLLATE tis620_thai_ci NULL DEFAULT NULL,
  `cur_dep` char(3) CHARACTER SET tis620 COLLATE tis620_thai_ci NULL DEFAULT NULL,
  `cur_time` datetime NULL DEFAULT NULL,
  `name` varchar(227) CHARACTER SET tis620 COLLATE tis620_thai_ci NULL DEFAULT NULL,
  `called_time` timestamp DEFAULT CURRENT_TIMESTAMP
);


CREATE  VIEW `view_ovst_doctor` AS SELECT
  `v`.`vn` AS `vn`,
  `v`.`hn` AS `hn`,
  `v`.`pt_priority` AS `pt_priority`,
  `v`.`oqueue` AS `oqueue`,
  `v`.`cur_dep` AS `cur_dep`,
  `v`.`cur_dep_time` AS `cur_dep_time`,
  concat(`p`.`pname`, ' ', `p`.`fname`, ' ', `p`.`lname`) AS `name`,
  `p`.`cid` AS `cid`,
  `k`.`department` AS `department`,
  `k`.`roomno` AS `roomno`,
  `xh`.`confirm_all` AS `confirm_all`,
  `l`.`lab_count` AS `lab_count`,
  `l`.`report_count` AS `report_count`,
  `ovq`.`pttype_check` AS `pttype_check`
FROM
  (
    (
      (
        (
          (`ovst` `v` LEFT JOIN `patient` `p` ON (`p`.`hn` = `v`.`hn`))
        LEFT JOIN `lab_status` `l` ON (`l`.`vn` = `v`.`vn`))
      LEFT JOIN `xray_head` `xh` ON (`xh`.`vn` = `v`.`vn`))
    LEFT JOIN `ovst_seq` `ovq` ON (`ovq`.`vn` = `v`.`vn`))
  LEFT JOIN `kskdepartment` `k` ON (`k`.`depcode` = `v`.`cur_dep`)) 
WHERE
  `v`.`cur_dep` <> '999' 
  AND `v`.`vstdate` = curdate() 
  AND `v`.`cur_dep_busy` = 'N' 
  AND ! (
  `v`.`hn` IN (SELECT DISTINCT `opdscreen`.`hn` FROM `opdscreen` WHERE `opdscreen`.`vstdate` = curdate() AND `opdscreen`.`waiting` = 'Y')) 
ORDER BY
  `v`.`pt_priority` DESC,
  `v`.`cur_dep_time`;