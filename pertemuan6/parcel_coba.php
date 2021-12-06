<?php

class Parameters
{
    const FILE_NAME = 'parcel_coba.txt'; 
    const COLUMNS  = ['item', 'price']; 
    const POPULATION_SIZE = 30;
    const BUDGET = 350000;
    const STOPPING_VALUE = 10000;
    const CROSOVER_RATE = 0.8;
    const MAX_ITER = 250;
}




class Catalogue{
    function createProductColum($listofRawProduct){
        foreach(array_keys($listofRawProduct) as $listofRawProductKey){
            $listofRawProduct[Parameters::COLUMNS[$listofRawProductKey]]= $listofRawProduct[$listofRawProductKey];
            unset($listofRawProduct[$listofRawProductKey]);
        }
        return $listofRawProduct;

    }

    function products(){
        $collectionOfListProduct = []; //disini

       $raw_data= file(Parameters::FILE_NAME);
       foreach ($raw_data as $listofRawProduct){
        $collectionOfListProduct[]= $this->createProductColum(explode(",",$listofRawProduct));
       }
       return $collectionOfListProduct;

    }

}

class Individu
{

    function countNumberOfGen()
    {
        $catalogue = new Catalogue; //disini
        return count($catalogue->products()); //ubah
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
    function createRandomPopulation()//ubah
    {
        $individu = new Individu; //tambah
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){ //ubah
           $ret[] = $individu->createRandomIndividu();
        }
        return $ret;

    }

}


class Fitness 
{
    function selectingItems($individu)
    {
        $catalogue = new Catalogue; //tambah
        foreach($individu as $individuKey => $binaryGen){
            if($binaryGen === 1){
                $ret[]= [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue->products()[$individuKey]['price'] //ubah
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
            return $fits[$index]; //ubah
            
        } else {
            foreach($fits as $key => $val){
                if ($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue'],
                        'chromosome' => $fits[$key]['chromosome']
                    ];
                }
            }

            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1 );
            }else{
                $max = max(array_column($ret, 'fitnessValue')); //tambah
                $index = array_search($max, array_column($ret, 'fitnessValue')); //ubah
            }
            // echo 'Hasil';
            return $ret[$index];
        }
    }

    function bestIndividus($fits)
    {
        $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        $maxItem = max(array_keys($countedMaxItems));
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];
        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem);
        return $bestFitnessValue;
    }


    // function isFound($fits)
    // {
    //    $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
    //    //print_r($countedMaxItems);
    //    //echo '<br>';
    //    $maxItem = max(array_keys($countedMaxItems));
    //    //echo $maxItem;
    //    //echo '<br>';
    //    //echo $countedMaxItems[$maxItem];
    //    $numberOfIndividuHasMaxItem =  $countedMaxItems[$maxItem];

    //    $bestFitnessValue = $this -> searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
    //    echo '<br>';
    //    echo '<br>Best fitness value: '.$bestFitnessValue;

    //    $residual = Parameters::BUDGET - $bestFitnessValue;
    //    echo ' Residual: '. $residual;

    //    if($residual <= Parameters::STOPPING_VALUE && $residual > 0){
    //        return TRUE;
    //    }

    // }

    function isFit($fitnessValue)
    {
        if ($fitnessValue <= Parameters::BUDGET){
            return TRUE;
        }
    }

    function fitnessEvaluation($population)
    {
        foreach ($population as $listOfIndividuKey => $listOfIndividu) {
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            if ($this->isFit($fitnessValue)) {
                $fits[] = [
                    'individuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue,
                    'chromosome' => $population[$listOfIndividuKey]
                ];
            }
        }
        return $fits;
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

    function randomizingParents()
    {
        for ($i = 0; $i < $this->popSize; $i++) {
            $randomZeroToOne = $this->randomZeroToOne();
            if ($randomZeroToOne < Parameters::CROSOVER_RATE) {
                $parents[$i] = $randomZeroToOne;
            }
        }
        return $parents;
    }

    function generateCrossover()
    {
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){ //tambah
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
        $lengthOfGen = new Individu; //tambah
        return rand(0, $lengthOfGen->countNumberOfGen()-1); //ubah
    }

    function crossover()
    {
       $cutPointIndex = $this->cutPointRandom();
       echo '<br>';
       foreach( $this -> generateCrossover() as $listOfCrossover){
           $parent1 = $this->populations[$listOfCrossover[0]];
           $parent2 = $this->populations[$listOfCrossover[1]];
           $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
           $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
           $offsprings[] = $offspring1;
           $offsprings[] = $offspring2;
       }
       return $offsprings;
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

    function isMutation() //tambah
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

    function isMutationExist()
    {
        if ($this->calculateNumOfMutation() > 0) {
            return TRUE;
        }
    }

    function mutation()
    {
        if ($this ->isMutation()) {
            for ($i =0; $i <= $this->calculateNumOfMutation()-1; $i++) {
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu(); //ubah
                $indexOfGen =  Randomizer::getRandomIndexOfGen();
                $selectedIndividu = $this->population[$indexOfIndividu];
                $valueOfGen = $selectedIndividu[$indexOfGen]; 
                $mutatedGen = $this->generateMutation($valueOfGen);
                $selectedIndividu[$indexOfGen] = $mutatedGen;
                $ret[] = $selectedIndividu;

           }
           return $ret;
        }
       

    }
}

class Selection
{
    function __construct($population, $combinedOffsprings)
    {
        $this->population = $population;
        $this -> combinedOffsprings = $combinedOffsprings;
    }

    function createTemporaryPopulation()
    {
       
        foreach ($this->combinedOffsprings as $offspring){
            $this->population[] = $offspring;
        }
       
        return $this->population;
    }

    function getVariableValue($basePopulation, $fitTemporaryPopulation)
    {
        
            foreach ($fitTemporaryPopulation as $val){
                $ret[] = $basePopulation[$val[1]];
            }
            return $ret;
        }
    

    function sortFitTemporaryPopulation()
    {
        $temPopulation = $this->createTemporaryPopulation();
        $fitness = new Fitness;
        foreach ($temPopulation as $key => $individu){
            $fitnessValue = $fitness->calculateFitnessValue($individu);
            if ($fitness->isfit($fitnessValue)){
                $fitTemporaryPopulation[] =[
                    $fitnessValue,
                    $key
                ];
            }
        }
        rsort($fitTemporaryPopulation);
        $fitTemporaryPopulation = array_slice($fitTemporaryPopulation, 0, Parameters::POPULATION_SIZE);
        return $this->getVariableValue($temPopulation, $fitTemporaryPopulation);
        

    }

    function selectingIndividus()
    {
       $selected =  $this->sortFitTemporaryPopulation();
       echo '<p></p>';
       print_r($selected);
    }
}



class Algen
{
    function __construct($popSize)
    {
        $this->popSize = $popSize;
    }
    function isFound($bestIndividus)
    {
        $residual = Parameters::BUDGET - $bestIndividus['fitnessValue'];
        if ($residual <= Parameters::STOPPING_VALUE && $residual > 0) {
            return TRUE;
        }
    }

    function countItems($chromosome)
    {
        return array_count_values($chromosome)[1];
    }

    function algen()
    {
        $fitness = new Fitness;
        $population = (new Population($this))->createRandomPopulation($this->popSize);
        $fitIndividus = $fitness->fitnessEvaluation($population);
        $bestIndividus = $fitness->bestIndividus($fitIndividus);
        $bestIndividuIsFound = $this->isFound($bestIndividus);

        $iter = 0;
        while ($iter < Parameters::MAX_ITER || $bestIndividuIsFound === FALSE) {

            $crossoverOffsprings = (new Crossover($population, $this->popSize))->crossover();
            $mutation = new Mutation($population, $this->popSize);

            if ($mutation->mutation($this->popSize)) {
                $mutationOffsprings = $mutation->mutation($this->popSize);
                foreach ($mutationOffsprings as $mutationOffspring) {
                    $crossoverOffsprings[] = $mutationOffspring;
                }
            }
            $selection = new Selection($population, $crossoverOffsprings, $this->popSize);
            $population = [];
            $population = $selection->selectingIndividus();
            $fitIndividus = [];
            $fitIndividus = $fitness->fitnessEvaluation($crossoverOffsprings);
            $bestIndividus = $fitness->bestIndividus($fitIndividus);

            $bestIndividuIsFound = $this->isFound($bestIndividus);

            if ($bestIndividuIsFound) {
                $bestIndividus['numOfItems'] = $this->countItems($bestIndividus['chromosome']);
                return $bestIndividus;
            }
            $bests[] = $bestIndividus;
            $iter++;
        }

        foreach ($bests as $key => $best) {
            $bests[$key]['numOfItems'] =  $this->countItems($best['chromosome']);
        }

        $maxItems = max(array_column($bests, 'numOfItems'));
        $index = array_search($maxItems, array_column($bests, 'numOfItems'));
        return $bests[$index];
    }
}

function saveToFile($popSize, $fitnessValue, $numOfItems)
{
    $pathToFile = 'parcel.txt';
    $data = array($popSize, $fitnessValue, $numOfItems);
    $fp = fopen($pathToFile, 'a');
    fputcsv($fp, $data);
    fclose($fp);
}

for ($popSize = 20; $popSize <= 250; $popSize+=40){
    for ($i = 0; $i < 10; $i++){
        echo 'PopSize: ' . $popSize;
        $algenKnapsack = (new Algen($popSize))->algen();
        echo ' Fitness: '.$algenKnapsack['fitnessValue'] . ' Items: ' . $algenKnapsack['numOfItems'];
        echo "\n";
        saveToFile($popSize, $algenKnapsack['fitnessValue'], $algenKnapsack['numOfItems']);
    }
}





$initalPopulation = new Population;
$population= $initalPopulation->createRandomPopulation();
$crossover = new Crossover($population);
$crossoverOffsprings= $crossover->crossover();

echo '<p></p>';
$mutation = new Mutation($population);
if ($mutation->mutation()){
    $mutaionOffsprings = $mutation->mutation();
    foreach ($mutaionOffsprings as $mutaionOffspring){
        $crossoverOffsprings[] = $mutaionOffspring;
    }
}


$selection = new Selection($population, $crossoverOffsprings);
$selection->selectingIndividus();


