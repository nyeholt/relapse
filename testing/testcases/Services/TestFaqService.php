<?php


class TestFaqService extends UnitTestCase 
{
    public function testFaqItemName()
    {
        Mock::generate('DbService');
        $dbService = new MockDbService();
        $faqService = new FaqService();
        $faqService->dbService = $dbService;
        
        $faq = new Faq();
        $params = array(
            'title' => 'My FAQ entry & _+',
        );
        
        $dbService->setReturnValue('saveObject', $faq);
        
        $result = $faqService->saveFaq($faq, $params);

        $this->assertEqual('my-faq-entry-and', $faq->faqurl);
    }

}
?>