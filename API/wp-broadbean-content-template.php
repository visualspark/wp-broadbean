<?php

include("template.class.php");

class wp_broadbean_content_template {

	private $_input;

	function __construct($input) {
		$this->_input = $input;
	}

	function generateContents() {
		$job = $this->_input->job[0];
	
		$info = $job["info"];
        $jobApply = $job["apply"];
		$infoTitle = $info[0]["title"];
		$infoTags = $info[0]["tags"];
		$infoLocation = $info[0]["location"];
		$infoIndustry = $info[0]["industry"];
		$infoType = $info[0]["type"];
		$infoDuration = $info[0]["duration"];
		$infoStart = $info[0]["duration"];
		$infoExtended = $info[0]["extended"];
		//$infoSkills = $info[0]["skills"];

		$date = $job["date"];
		$dateCreate = $date[0]["create"];

		$salary = $job["salary"];
		$salaryExtended = $salary[0]["extended"];
		$salaryCurrency = $salary[0]["currency"];
		$salaryFrom = $salary[0]["from"];
		$salaryTo = $salary[0]["to"];
		$salaryPer = $salary[0]["per"];
		$salaryBenifits= $salary[0]["benifits"];

		$reference = $job["reference"];

		$contact = $job["contact"];
		$contactName = $contact[0]["name"];
		$contactEmail = $contact[0]["email"];
		$contactPhone = $contact[0]["phone"];

		$jobId = $job["id"];
		$summaries = $job["summary"];
        $summaryText ="";
		foreach ($summaries as $summary) {
			$summaryPoint = $summary["point"];
			$summaryText .= "<li><strong>$summaryPoint</strong></li>";
		}
        
//		$skillsText = "";
//		$skills = explode(",", $infoSkills);
//		foreach ($skills as $skill) {
//			$skillsText .= "<li>$skill</li>";
//		}

		$layout = new Template("main.tpl");
        
        $layout->set("job.id", $jobId);
        $layout->set("job.apply", $jobApply);
        $layout->set("job.summaries", $summaryText);
        $layout->set("job.reference", $reference);
		
		$layout->set("job.info.title", $infoTitle);
		$layout->set("job.info.tags", $infoTags);
		$layout->set("job.info.location", $infoLocation);
		$layout->set("job.info.industry", $infoIndustry);
		$layout->set("job.info.type", $infoType);
		$layout->set("job.info.duration", $infoDuration);
		$layout->set("job.info.start", $infoStart);
		$layout->set("job.info.extended", $infoExtended);
        //$layout->set("job.info.skills", $skillsText);
		
        $layout->set("job.date.create", $dateCreate);

		$layout->set("job.contact.name", $contactName);
		$layout->set("job.contact.phone", $contactPhone);
		$layout->set("job.contact.email", $contactEmail);
		
        $layout->set("job.salary.currency", $salaryCurrency);
		$layout->set("job.salary.from", $salaryFrom);
		$layout->set("job.salary.to", $salaryTo);
		$layout->set("job.salary.per", $salaryPer);
		$layout->set("job.salary.benifits", $salaryBenifits);
        $layout->set("job.salary.extended", $salaryExtended);
        
		return $layout->output();
	}	
}

?>