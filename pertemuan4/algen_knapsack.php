<?php

class Parameters
{
    const FILE_NAME = 'products.txt'; 
    const COLUMNS  = ['item', 'price']; 
    const POPULATION_SIZE = 30;
    const BUDGET = 350000;
    const STOPPING_VALUE = 10000;
    const CROSOVER_RATE = 0.8;
}




class catalogue{
    function createProductColum($listofRawProduct){
        foreach(array_keys($listofRawProduct) as $listofRawProductKey){
            $listofRawProduct[Parameters::COLUMNS[$listofRawProductKey]]= $listofRawProduct[$listofRawProductKey];
            unset($listofRawProduct[$listofRawProductKey]);
        }
        return $listofRawProduct;

    }

    function products(){
        $koleksiListProdak = [];

       $raw_data= file(Parameters::FILE_NAME);
       foreach ($raw_data as $listofRawProduct){
           $koleksiListProdak[]= $this->createProductColum(explode(",",$listofRawProduct));
       }
       return $koleksiListProdak;

    }

}

class Individu
{

    function countNumberOfGen()
    {
        $catalogue = new Catalogue;
        return count($catalogue->products());
    }

    function createRandomIndividu()
    {
        for ($i = 0; $i <= $this->countNumberOfGen()-1; $i++) {
            $ret[] = rand(0, 1);
        }
        return $ret;
    }
}

class Population
{
    function createRandomPopulation()
    {
        $individu = new Individu;
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
           $ret[] = $individu->createRandomIndividu();
        }
        return $ret;

    }

}


class Fitness 
{
    function selectingItems($individu)
    {
        $catalogue = new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if($binaryGen === 1){
                $ret[]= [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue->products()[$individuKey]['price']
                ];
            }
        }
        return $ret;
    }

    function calculateFitnessValue($individu)
    {
     return array_sum(array_column($this->selectingItems($individu), 'selectedPrice'));
      
    }

    function countSelectedItem($individu)
    {
        return count($this->selectingItems($individu));
    }

    function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)
    {
        if($numberOfIndividuHasMaxItem === 1){
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            return $fits[$index];
            
        } else {
            foreach($fits as $key => $val){
                if ($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }

            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1 );
            }else{
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }
            echo 'Hasil';
            return $ret[$index];
        }
    }

    function isFound($fits)
    {
       $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
       print_r($countedMaxItems);
       echo '<br>';
       $maxItem = max(array_keys($countedMaxItems));
       echo $maxItem;
       echo '<br>';
       echo $countedMaxItems[$maxItem];
       $numberOfIndividuHasMaxItem =  $countedMaxItems[$maxItem];

       $bestFitnessValue = $this -> searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
       echo '<br>';
       echo '<br>Best fitness value: '.$bestFitnessValue;

       $residual = Parameters::BUDGET - $bestFitnessValue;
       echo ' Residual: '. $residual;

       if($residual <= Parameters::STOPPING_VALUE && $residual > 0){
           return TRUE;
       }

    }

    function isFit($fitnessValue)
    {
        if ($fitnessValue <= Parameters::BUDGET){
            return TRUE;
        }
    }

    function fitnessEvaluation($population)
    {
        $catalogue = new Catalogue;
        foreach ($population as $listOfIndividuKey => $listOfIndividu){
            echo 'Individu-'. $listOfIndividuKey. '<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen. '&nbsp;&nbsp;';
                print_r($catalogue->products()[$individuKey]);
                echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            echo 'Max. Item: '. $numberOfSelectedItem;
            echo ' Fitness value: '. $fitnessValue;

            if ($this->isFit($fitnessValue)){
                echo ' (Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits);
            }else{
                echo ' (Not Fit)';
            }
           
            echo '<p>';
        }
       if ( $this->isFound($fits)){
           echo ' Found';
       }else{
           echo ' >> Next generation';
       }
    }
    
}



class Crossover 
{
    public $population;

    function __construct($populations)
    {
        $this->populations = $populations;
    }

    function randomZeroToOne()
    {
        return (float) rand() / (float) getrandmax();
    }

    function generateCrossover()
    {
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
            $randomZeroToOne = $this->randomZeroToOne();
            if ($randomZeroToOne < Parameters::CROSOVER_RATE){
                $parents[$i] = $randomZeroToOne;
            }
        }
       foreach (array_keys($parents) as $key){
           foreach (array_keys($parents) as $subkey){
               if ($key !== $subkey) {
                   $ret[] = [$key, $subkey];
               }
           }
           array_shift($parents);
       }
       return $ret;
    }

    function offspring($parent1, $parent2, $cutPointIndex, $offspring)
    {
        $lengthOfGen = new Individu;
        if ($offspring === 1){
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen()-1; $i++){
                if ($i <= $cutPointIndex){
                    $ret[] = $parent1[$i];
                }
            if ($i > $cutPointIndex){
                $ret[] = $parent2[$i];
            }
        }

    }

        if ($offspring === 2){
            for ($i = 0; $i <= $lengthOfGen->countNumberOfGen()-1; $i++){
                if ($i <= $cutPointIndex){
                    $ret[] = $parent2[$i];
                }
                if ($i > $cutPointIndex){
                     $ret[] = $parent1[$i];
                    }
                }
        
         }
        return $ret;
    }
    function cutPointRandom()
    {
        $lengthOfGen = new Individu;
        return rand(0, $lengthOfGen->countNumberOfGen()-1);
    }

    function crossover()
    {
       $cutPointIndex = $this->cutPointRandom();
       echo '<br>';
       echo $cutPointIndex;
       foreach( $this -> generateCrossover() as $listOfCrossover){
           $parent1 = $this->populations[$listOfCrossover[0]];
           $parent2 = $this->populations[$listOfCrossover[1]];
        //    echo '<p> <p>';
        //    echo 'Parents :<br>';
        //    foreach ($parent1 as $gen){
        //        echo $gen;
        //    }
        //    echo '><';
        //    foreach ($parent2 as $gen){
        //        echo $gen;
        //    }
        //    echo '<br>';

        //    echo 'offspring<br>';
           $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
           $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
        //    foreach ($offspring1 as $gen){
        //     echo $gen;
        //  }
        //      echo '><';
        //     foreach ($offspring2 as $gen){
        //     echo $gen;
        //      }

        $offsprings[] = $offspring1;
        $offsprings[] = $offspring2;
       }
       return $offsprings;
    }

}

class Randomizer
{
    static function getRandomIndexOfGen(){
        return rand (0, (new Individu())->countNumberOfGen() -1);
    }

    static function getRandomIndexOfIndividu(){
        return rand(0, Parameters::POPULATION_SIZE - 1);
    }
}


class Mutation 
{
    function __construct($population)
    {
        $this ->population = $population;
    }

    function calculateMutationRate()
    {
        return 1/ (new Individu())->countNumberOfGen();
    }

    function calculateNumOfMutation()
    {
        return round($this->calculateMutationRate() * Parameters::POPULATION_SIZE);
    }

    function isMutation()
    {
        if ($this->calculateNumOfMutation() > 0) {
            return TRUE;
        }
    }

    function generateMutation($valueOfGen)
    {
        if ($valueOfGen === 0){
            return 1;
        }else{
            return 0;
        }
    }

    function mutation()
    {
        if ($this ->isMutation()) {
            for ($i =0; $i <= $this->calculateNumOfMutation()-1; $i++) {
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
                $indexOfGen =  Randomizer::getRandomIndexOfGen();
                $selectedIndividu = $this->population[$indexOfIndividu];


                echo 'Before mutation: ';
                print_r($selectedIndividu);
                echo '<br>';
                $valueOfGen = $selectedIndividu[$indexOfGen]; 
                $mutatedGen = $this->generateMutation($valueOfGen);
                $selectedIndividu[$indexOfGen] = $mutatedGen;
                echo 'After mutation: ';
                print_r($selectedIndividu);
                $ret[] = $selectedIndividu;

           }
           return $ret;
        }
       

    }
}



// $ktalog = new catalogue;
// $ktalog->products($parameter);

$initalPopulation = new Population;
$population= $initalPopulation->createRandomPopulation();


// $fitness = new Fitness;
// $fitness->fitnessEvaluation($population);

$crossover = new Crossover($population);
$crossoverOffsprings= $crossover->crossover();

echo 'Cross offspring:<br>';
print_r($crossoverOffsprings);

echo '<p></p>';
// (new Mutation($population))->mutation();
$mutation = new Mutation($population);
if ($mutation->mutation()){
    $mutaionOffsprings = $mutation->mutation();
    echo 'Mutation offspring<br>';
    print_r($mutaionOffsprings);
    echo '<p></p>';
    foreach ($mutaionOffsprings as $mutaionOffspring){
        $crossoverOffsprings[] = $mutaionOffspring;
    }
}

echo 'Mutation offsprings <br>';
print_r($crossoverOffsprings);

// $individu = new Individu;
// print_r($individu->createRandomIndividu());

