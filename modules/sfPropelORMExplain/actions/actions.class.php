<?php

class sfPropelORMExplainActions extends sfActions
{
  public function executeIndex(sfWebRequest $request)
  {
      $this->setLayout(false);
      sfConfig::set('sf_web_debug', false);

      // Open the connection
      $con = \Propel::getConnection($request->getParameter('connection'));

      // Get the adapter
      $db = \Propel::getDB($request->getParameter('connection'));

      try {
        $this->query = base64_decode($request->getParameter('base64_query'));
        $stmt = $db->doExplainPlan($con, $this->query);
        $this->results = $stmt->fetchAll(\PDO::FETCH_ASSOC);
      } catch (Exception $e) {
        $this->getResponse()->setContent('<div class="error">This query cannot be explained.</div>');
        return sfView::NONE;
      }
  }
}
